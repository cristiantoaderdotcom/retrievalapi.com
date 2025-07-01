<?php

namespace App\Services\ProductCatalog;

use App\Enums\ProductFeedStatus;
use App\Jobs\KnowledgeBase\GenerateProductEmbeddingJob;
use App\Models\Product;
use App\Models\ProductFeed;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductFeedProcessor
{
    /**
     * Process a product feed.
     *
     * @param ProductFeed $feed
     * @return bool
     */
    public function process(ProductFeed $feed): bool
    {
        if (!$feed->isDueForProcessing()) {
            return false;
        }

        // Mark feed as processing
        $feed->update([
            'status' => ProductFeedStatus::PROCESSING,
            'error_message' => null,
        ]);

        try {
            // Process products based on provider
            if ($feed->provider === 'shopify') {
                $this->processShopifyFeedWithPagination($feed);
            } else {
                throw new \Exception("Unsupported provider: {$feed->provider}");
            }

            // Update last processed timestamp
            $feed->update([
                'status' => ProductFeedStatus::IDLE,
                'last_processed_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error processing feed {$feed->id}: " . $e->getMessage(), [
                'feed_id' => $feed->id,
                'workspace_id' => $feed->workspace_id,
                'exception' => $e,
            ]);

            $feed->update([
                'status' => ProductFeedStatus::ERROR,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process Shopify feed with pagination support.
     *
     * @param ProductFeed $feed
     * @return void
     */
    protected function processShopifyFeedWithPagination(ProductFeed $feed): void
    {
        $page = 1;
        $limit = 250;
        $totalProcessed = 0;

        do {
            Log::info("Processing page {$page} for feed {$feed->id}");
            
            // Build URL with pagination parameters
            $url = $this->buildPaginatedUrl($feed->url, $limit, $page);
            
            // Fetch page content
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch feed page {$page}: " . $response->status());
            }

            $data = $response->json();
            
            // Process products from this page
            $productsProcessed = $this->processShopifyProducts($feed, $data);
            $totalProcessed += $productsProcessed;
            
            Log::info("Processed {$productsProcessed} products from page {$page} (total: {$totalProcessed})");
            
            // If we got fewer products than the limit, we've reached the end
            if ($productsProcessed < $limit) {
                break;
            }
            
            $page++;
            
            // Safety check to prevent infinite loops
            if ($page > 1000) {
                Log::warning("Reached maximum page limit (1000) for feed {$feed->id}");
                break;
            }
            
        } while (true);

        Log::info("Completed processing feed {$feed->id}. Total products processed: {$totalProcessed}");
    }

    /**
     * Build paginated URL with limit and page parameters.
     *
     * @param string $baseUrl
     * @param int $limit
     * @param int $page
     * @return string
     */
    protected function buildPaginatedUrl(string $baseUrl, int $limit, int $page): string
    {
        $parsedUrl = parse_url($baseUrl);
        $query = [];
        
        // Parse existing query parameters
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        
        // Add/override pagination parameters
        $query['limit'] = $limit;
        $query['page'] = $page;
        
        // Rebuild URL
        $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        if (isset($parsedUrl['port'])) {
            $url .= ':' . $parsedUrl['port'];
        }
        
        if (isset($parsedUrl['path'])) {
            $url .= $parsedUrl['path'];
        }
        
        $url .= '?' . http_build_query($query);
        
        if (isset($parsedUrl['fragment'])) {
            $url .= '#' . $parsedUrl['fragment'];
        }
        
        return $url;
    }

    /**
     * Process Shopify products.
     *
     * @param ProductFeed $feed
     * @param array $data
     * @return int Number of products processed
     */
    protected function processShopifyProducts(ProductFeed $feed, array $data): int
    {
        $productsProcessed = 0;
        
        // Check the structure of the data
        if (isset($data['products']) && is_array($data['products'])) {
            // Format is { "products": [...] }
            foreach ($data['products'] as $productData) {
                $this->processShopifyProduct($feed, $productData);
                $productsProcessed++;
            }
        } else if (array_key_exists(0, $data) && is_array($data[0])) {
            // Format is a direct array of products [product1, product2, ...]
            foreach ($data as $productData) {
                $this->processShopifyProduct($feed, $productData);
                $productsProcessed++;
            }
        } else if (isset($data['id'])) {
            // Single product object
            $this->processShopifyProduct($feed, $data);
            $productsProcessed = 1;
        } else {
            // Empty response or unrecognized format
            if (empty($data) || (is_array($data) && count($data) === 0)) {
                Log::info("Empty response received, assuming end of pages");
                return 0;
            } else {
                throw new \Exception("Unrecognized product data format. Expected array of products or single product.");
            }
        }
        
        return $productsProcessed;
    }

    /**
     * Process a single Shopify product.
     *
     * @param ProductFeed $feed
     * @param array $productData
     * @return void
     */
    protected function processShopifyProduct(ProductFeed $feed, array $productData): void
    {
        // Ensure the product data has an ID
        if (!isset($productData['id'])) {
            Log::warning("Skipping product without ID", ['product' => $productData]);
            return;
        }

        DB::transaction(function () use ($feed, $productData) {
            // Create or update product
            $product = Product::updateOrCreate(
                [
                    'workspace_id' => $feed->workspace_id,
                    'external_id' => (string) $productData['id'],
                ],
                [
                    'product_feed_id' => $feed->id,
                    'title' => $productData['title'],
                    'handle' => $productData['handle'] ?? null,
                    'body_html' => $productData['body_html'] ?? null,
                    'published_at' => isset($productData['published_at']) ? Carbon::parse($productData['published_at']) : null,
                    'external_created_at' => isset($productData['created_at']) ? Carbon::parse($productData['created_at']) : null,
                    'external_updated_at' => isset($productData['updated_at']) ? Carbon::parse($productData['updated_at']) : null,
                    'vendor' => $productData['vendor'] ?? null,
                    'product_type' => $productData['product_type'] ?? null,
                    'tags' => isset($productData['tags']) ? (is_array($productData['tags']) ? $productData['tags'] : explode(', ', $productData['tags'])) : null,
                ]
            );

            // Process variants
            if (isset($productData['variants']) && is_array($productData['variants'])) {
                $this->processVariants($product, $productData['variants']);
            }

            // Process images
            if (isset($productData['images']) && is_array($productData['images'])) {
                $this->processImages($product, $productData['images']);
            }

            // Process options
            if (isset($productData['options']) && is_array($productData['options'])) {
                $this->processOptions($product, $productData['options']);
            }

            // Dispatch GenerateProductEmbeddingJob
            GenerateProductEmbeddingJob::dispatch($product);
        });
    }

    /**
     * Process product variants.
     *
     * @param Product $product
     * @param array $variants
     * @return void
     */
    protected function processVariants(Product $product, array $variants): void
    {
        $existingVariantIds = $product->variants()->pluck('external_id')->toArray();
        $processedVariantIds = [];

        foreach ($variants as $variantData) {
            // Skip if no ID
            if (!isset($variantData['id'])) {
                continue;
            }

            $variantId = (string) $variantData['id'];
            $processedVariantIds[] = $variantId;

            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'external_id' => $variantId,
                ],
                [
                    'title' => $variantData['title'] ?? '',
                    'option1' => $variantData['option1'] ?? null,
                    'option2' => $variantData['option2'] ?? null,
                    'option3' => $variantData['option3'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'requires_shipping' => $variantData['requires_shipping'] ?? true,
                    'taxable' => $variantData['taxable'] ?? true,
                    'available' => $variantData['available'] ?? true,
                    'price' => $variantData['price'] ?? null,
                    'grams' => $variantData['grams'] ?? null,
                    'compare_at_price' => $variantData['compare_at_price'] ?? null,
                    'position' => $variantData['position'] ?? null,
                    'external_created_at' => isset($variantData['created_at']) ? Carbon::parse($variantData['created_at']) : null,
                    'external_updated_at' => isset($variantData['updated_at']) ? Carbon::parse($variantData['updated_at']) : null,
                ]
            );
        }

        // Remove variants that no longer exist
        $variantsToDelete = array_diff($existingVariantIds, $processedVariantIds);
        if (!empty($variantsToDelete)) {
            $product->variants()->whereIn('external_id', $variantsToDelete)->delete();
        }
    }

    /**
     * Process product images.
     *
     * @param Product $product
     * @param array $images
     * @return void
     */
    protected function processImages(Product $product, array $images): void
    {
        $existingImageIds = $product->images()->pluck('external_id')->toArray();
        $processedImageIds = [];

        foreach ($images as $imageData) {
            // Skip if no ID or src
            if (!isset($imageData['id']) || !isset($imageData['src'])) {
                continue;
            }

            $imageId = (string) $imageData['id'];
            $processedImageIds[] = $imageId;

            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'external_id' => $imageId,
                ],
                [
                    'position' => $imageData['position'] ?? null,
                    'src' => $imageData['src'],
                    'width' => $imageData['width'] ?? null,
                    'height' => $imageData['height'] ?? null,
                    'variant_ids' => $imageData['variant_ids'] ?? null,
                    'external_created_at' => isset($imageData['created_at']) ? Carbon::parse($imageData['created_at']) : null,
                    'external_updated_at' => isset($imageData['updated_at']) ? Carbon::parse($imageData['updated_at']) : null,
                ]
            );
        }

        // Remove images that no longer exist
        $imagesToDelete = array_diff($existingImageIds, $processedImageIds);
        if (!empty($imagesToDelete)) {
            $product->images()->whereIn('external_id', $imagesToDelete)->delete();
        }
    }

    /**
     * Process product options.
     *
     * @param Product $product
     * @param array $options
     * @return void
     */
    protected function processOptions(Product $product, array $options): void
    {
        // First delete all existing options for this product
        $product->options()->delete();

        // Then create new ones
        foreach ($options as $optionData) {
            if (!isset($optionData['name']) || !isset($optionData['position']) || !isset($optionData['values'])) {
                continue;
            }

            ProductOption::create([
                'product_id' => $product->id,
                'name' => $optionData['name'],
                'position' => $optionData['position'],
                'values' => $optionData['values'],
            ]);
        }
    }
} 