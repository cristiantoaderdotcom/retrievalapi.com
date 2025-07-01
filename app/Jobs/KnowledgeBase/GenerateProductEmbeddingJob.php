<?php

namespace App\Jobs\KnowledgeBase;

use App\Models\Product;
use App\Services\Ai\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use OpenAI;

class GenerateProductEmbeddingJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    
    private Product $product;
    private $similarityThreshold = 0.8; // Threshold for considering products too similar

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->onQueue('low');
    }

    /**
     * Get backoff times for retries.
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        // Skip if this job is part of a cancelled batch
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        // Skip if already processed
        if ($this->product->embedding_processed_at) {
            Log::info("Product {$this->product->id} already has embeddings, skipping.");
            return;
        }

        // Prepare content for embedding
        $content = $this->prepareProductContent($this->product);
        
        if (empty($content)) {
            Log::warning("Product {$this->product->id} has no content to embed.");
            return;
        }

        try {
            $openai = OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->withOrganization(config('services.openai.organization'))
                ->make();
            
            $response = $openai->embeddings()->create([
                'model' => 'text-embedding-3-large',
                'input' => $content
            ]);
            
            // Update product with embedding
            $this->product->embedding = json_encode($response->embeddings[0]->embedding);
            $this->product->embedding_processed_at = now();
            $this->product->timestamps = false;
            $this->product->save();
            
            Log::info("Successfully generated embedding for product {$this->product->id}.");
            
            // After embedding is created, check for similar products
            $this->checkForSimilarProducts($embeddingService);
            
        } catch (\Exception $e) {
            Log::error("Error generating embedding for product {$this->product->id}: " . $e->getMessage(), [
                'product_id' => $this->product->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Check if this product is too similar to other existing products
     * and log similarity information
     */
    private function checkForSimilarProducts(EmbeddingService $embeddingService): void
    {
        // Get workspace ID of this product
        $workspaceId = $this->product->workspace_id;
        
        // Skip products without workspace
        if (!$workspaceId) {
            return;
        }
        
        // Get all other products in the same workspace that already have embeddings
        // excluding the current product
        $otherProducts = Product::where('workspace_id', $workspaceId)
            ->where('id', '!=', $this->product->id)
            ->whereNotNull('embedding')
            ->whereNotNull('embedding_processed_at')
            ->get();
        
        // Skip if no other products to compare with
        if ($otherProducts->isEmpty()) {
            Log::info("No other products to compare with for product {$this->product->id}");
            return;
        }
        
        // Get the current product embedding
        $currentEmbedding = json_decode($this->product->embedding, true);
        if (empty($currentEmbedding)) {
            return;
        }
        
        $highestSimilarity = 0;
        $mostSimilarProductId = null;
        $mostSimilarProduct = null;
        $totalSimilarity = 0;
        $validComparisons = 0;
        
        // Check similarity with each existing product
        foreach ($otherProducts as $otherProduct) {
            $otherEmbedding = json_decode($otherProduct->embedding, true);
            if (empty($otherEmbedding)) {
                continue;
            }
            
            // Calculate similarity
            $similarity = $embeddingService->calculateCosineSimilarity($currentEmbedding, $otherEmbedding);
            
            // Add to total for average calculation
            $totalSimilarity += $similarity;
            $validComparisons++;
            
            // Track highest similarity
            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $mostSimilarProductId = $otherProduct->id;
                $mostSimilarProduct = $otherProduct;
            }
        }
        
        // Calculate average similarity
        $averageSimilarity = $validComparisons > 0 ? $totalSimilarity / $validComparisons : 0;
        
        Log::info("Product similarity analysis completed", [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'avg_similarity_score' => $averageSimilarity,
            'highest_similarity' => $highestSimilarity,
            'most_similar_to' => $mostSimilarProductId,
            'most_similar_title' => $mostSimilarProduct?->title,
            'comparisons' => $validComparisons,
            'workspace_id' => $workspaceId
        ]);
        
        // Log warning if products are very similar
        if ($highestSimilarity >= $this->similarityThreshold) {
            Log::warning("Very similar products detected", [
                'product_id' => $this->product->id,
                'similar_to_product_id' => $mostSimilarProductId,
                'similarity_score' => $highestSimilarity,
                'product_title' => $this->product->title,
                'similar_product_title' => $mostSimilarProduct->title,
                'workspace_id' => $workspaceId
            ]);
        }
    }

    /**
     * Prepare product content for embedding.
     *
     * @param Product $product
     * @return string
     */
    protected function prepareProductContent(Product $product): string
    {
        $content = [];
        
        // Add title
        $content[] = "Product Name: " . $product->title;

        // Add product URL with handle
        if ($product->handle && $product->feed) {
            $domain = parse_url($product->feed->url, PHP_URL_HOST);
            if ($domain) {
                $content[] = "Product URL: https://{$domain}/products/{$product->handle}";
            }
        }

        // Add description (body_html)
        if ($product->body_html) {
            $content[] = "Description: " . strip_tags($product->body_html);
        }
        
        // Add vendor
        if ($product->vendor) {
            $content[] = "Vendor: " . $product->vendor;
        }
        
        // Add product type
        if ($product->product_type) {
            $content[] = "Product Type: " . $product->product_type;
        }
        
        // Add tags
        if ($product->tags && count($product->tags) > 0) {
            $content[] = "Tags: " . implode(', ', $product->tags);
        }

        // Add product options
        $options = $product->options;
        if ($options && $options->isNotEmpty()) {
            foreach ($options as $option) {
                if ($option->name && !empty($option->values)) {
                    $content[] = "Option {$option->name}: " . implode(', ', $option->values);
                }
            }
        }
        
        // Add variants information
        $variants = $product->variants;
        if ($variants && $variants->isNotEmpty()) {
            $variantInfo = [];
            foreach ($variants as $variant) {
                $variantDetails = [];
                if ($variant->title) $variantDetails[] = $variant->title;
                if ($variant->option1) $variantDetails[] = $variant->option1;
                if ($variant->option2) $variantDetails[] = $variant->option2;
                if ($variant->option3) $variantDetails[] = $variant->option3;
                if ($variant->sku) $variantDetails[] = "SKU: " . $variant->sku;
                
                $variantInfo[] = implode(', ', $variantDetails);
            }
            $content[] = "Variants: " . implode(' | ', $variantInfo);
        }
        
        return implode("\n", $content);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateProductEmbeddingJob failed for product #{$this->product->id}: " . $exception->getMessage(), [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'workspace_id' => $this->product->workspace_id,
            'batch_id' => $this->batch() ? $this->batch()->id : null,
            'exception' => $exception,
        ]);
    }
} 