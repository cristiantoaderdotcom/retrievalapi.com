<?php

namespace App\Services\Rag;

use Illuminate\Support\Facades\Log;
use OpenAI;

class EmbeddingService
{
    /**
     * Create embedding for text using OpenAI API
     * 
     * @param string $text Text to create embedding for
     * @return array|null The embedding vector or null on failure
     */
    public function createEmbedding(string $text): ?array
    {
        try {
            $openai = OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->withOrganization(config('services.openai.organization'))
                ->make();
            
            $response = $openai->embeddings()->create([
                'model' => 'text-embedding-3-large',
                'input' => $text
            ]);
            
            // Access embedding from the response structure
            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('Error creating embedding: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create embeddings for multiple contexts in batches
     * 
     * @param \Illuminate\Support\Collection $contexts Contexts to create embeddings for
     * @return void
     */
    public function createEmbeddingsForContexts($contexts): void
    {
        // Process in batches of 20 to avoid API limits
        $batches = $contexts->chunk(20);
        
        foreach ($batches as $batch) {
            // Process each context individually to avoid formatting issues
            foreach ($batch as $context) {
                try {
                    // Check if context is a KnowledgeBase model
                    if ($context instanceof \App\Models\KnowledgeBase) {
                        $text = $context->getTextForEmbedding();
                    } else {
                        $text = empty($context->context) ? $context->question : $context->context;
                    }
                    
                    $openai = OpenAI::factory()
                        ->withApiKey(config('services.openai.key'))
                        ->withOrganization(config('services.openai.organization'))
                        ->make();
                    
                    $response = $openai->embeddings()->create([
                        'model' => 'text-embedding-3-large',
                        'input' => $text
                    ]);
                    
                    // Store embedding
                    $context->embedding = json_encode($response->embeddings[0]->embedding);
                    $context->embedding_processed_at = now();
                    $context->timestamps = false;
                    $context->save();
                    
                } catch (\Exception $e) {
                    Log::error('Error creating batch embedding: ' . $e->getMessage());
                    // Continue with next context if this one fails
                }
                
                // Small pause to prevent rate limiting
                usleep(200000); // 200ms
            }
        }
    }

    /**
     * Create embeddings for multiple products in batches
     * 
     * @param \Illuminate\Support\Collection $products Products to create embeddings for
     * @return void
     */
    public function createEmbeddingsForProducts($products): void
    {
        // Process in batches of 20 to avoid API limits
        $batches = $products->chunk(20);
        
        foreach ($batches as $batch) {
            // Process each product individually to avoid formatting issues
            foreach ($batch as $product) {
                try {
                    // Create text representation of product for embedding
                    $text = $this->getProductTextForEmbedding($product);
                    
                    $openai = OpenAI::factory()
                        ->withApiKey(config('services.openai.key'))
                        ->withOrganization(config('services.openai.organization'))
                        ->make();
                    
                    $response = $openai->embeddings()->create([
                        'model' => 'text-embedding-3-large',
                        'input' => $text
                    ]);
                    
                    // Store embedding
                    $product->embedding = json_encode($response->embeddings[0]->embedding);
                    $product->embedding_processed_at = now();
                    $product->timestamps = false;
                    $product->save();
                    
                } catch (\Exception $e) {
                    Log::error('Error creating product embedding: ' . $e->getMessage());
                    // Continue with next product if this one fails
                }
                
                // Small pause to prevent rate limiting
                usleep(200000); // 200ms
            }
        }
    }

    /**
     * Generate text representation of a product for embedding
     * 
     * @param \App\Models\Product $product The product to process
     * @return string Text representation for embedding
     */
    private function getProductTextForEmbedding($product): string
    {
        $text = [];
        
        // Add product title and body
        $text[] = "Product: " . $product->title;
        if (!empty($product->body_html)) {
            $text[] = "Description: " . strip_tags($product->body_html);
        }
        
        // Add product type and vendor
        if (!empty($product->product_type)) {
            $text[] = "Type: " . $product->product_type;
        }
        if (!empty($product->vendor)) {
            $text[] = "Vendor: " . $product->vendor;
        }
        
        // Add tags
        if (!empty($product->tags) && is_array($product->tags)) {
            $text[] = "Tags: " . implode(", ", $product->tags);
        }
        
        return implode(" ", $text);
    }

    /**
     * Calculate cosine similarity between two vectors
     * 
     * @param array $a First vector
     * @param array $b Second vector
     * @return float Cosine similarity between 0 and 1
     */
    public function calculateCosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;
        
        foreach ($a as $i => $valueA) {
            $valueB = $b[$i] ?? 0;
            $dotProduct += $valueA * $valueB;
            $magnitudeA += $valueA * $valueA;
            $magnitudeB += $valueB * $valueB;
        }
        
        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);
        
        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Find similar contexts to the user query embedding
     * 
     * @param array $userEmbedding The user query embedding
     * @param \Illuminate\Support\Collection $contexts All available contexts
     * @return array Array of similar contexts with their IDs and similarity scores
     */
    public function findSimilarContexts(array $userEmbedding, $contexts): array
    {
        $similarities = [];
        
        foreach ($contexts as $context) {
            // Skip contexts without embeddings
            if (empty($context->embedding)) {
                continue;
            }
            
            $contextEmbedding = json_decode($context->embedding, true);
            if (!$contextEmbedding) {
                continue;
            }
            
            // Calculate cosine similarity
            $similarity = $this->calculateCosineSimilarity($userEmbedding, $contextEmbedding);
            
            // Store all similarities for threshold calculation
            $similarities[] = [
                'id' => $context->id,
                'similarity' => $similarity,
                'question' => $context->question
            ];
        }
        
        // Sort by similarity (highest first)
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        $topContexts = [];
        
        // Include strong matches (similarity > 0.8) - lowered from 0.9
        foreach ($similarities as $item) {
            if ($item['similarity'] >= 0.8) {
                $topContexts[] = $item;
            }
        }
        
        // Calculate a dynamic threshold based on the highest similarity
        if (count($similarities) > 0) {
            $highestSimilarity = $similarities[0]['similarity'];
            // More inclusive threshold: lower minimum (0.4) and bigger gap (0.25)
            $dynamicThreshold = max(0.4, $highestSimilarity - 0.25); 
            
            // Add any contexts above the dynamic threshold that weren't already added
            foreach ($similarities as $item) {
                if ($item['similarity'] >= $dynamicThreshold && 
                    !in_array($item['id'], array_column($topContexts, 'id'))) {
                    $topContexts[] = $item;
                }
            }
        }
        
        // If we don't have at least 10 contexts yet, add more from the sorted list
        if (count($topContexts) < 10 && count($similarities) > 0) {
            $additionalContextsNeeded = 10 - count($topContexts);
            $existingIds = array_column($topContexts, 'id');
            
            // Find contexts not already included
            $remainingContexts = array_filter($similarities, function($item) use ($existingIds) {
                return !in_array($item['id'], $existingIds);
            });
            
            // Sort remaining contexts by similarity (should already be sorted, but ensuring)
            usort($remainingContexts, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            // Take the top N remaining contexts
            $additionalContexts = array_slice($remainingContexts, 0, $additionalContextsNeeded);
            
            // Add these to our results
            foreach ($additionalContexts as $context) {
                $topContexts[] = $context;
            }
        }
        
        // Cap at 15 max contexts (was 10)
        $topContexts = array_slice($topContexts, 0, length: 7);
        
        return $topContexts;
    }

    /**
     * Find similar products to the user query embedding
     * 
     * @param array $userEmbedding The user query embedding
     * @param \Illuminate\Support\Collection $products All available products
     * @return array Array of similar products with their IDs and similarity scores
     */
    public function findSimilarProducts(array $userEmbedding, $products): array
    {
        $similarities = [];
        
        foreach ($products as $product) {
            // Skip products without embeddings
            if (empty($product->embedding)) {
                continue;
            }
            
            $productEmbedding = json_decode($product->embedding, true);
            if (!$productEmbedding) {
                continue;
            }
            
            // Calculate cosine similarity
            $similarity = $this->calculateCosineSimilarity($userEmbedding, $productEmbedding);
            
            // Store all similarities for threshold calculation
            $similarities[] = [
                'id' => $product->id,
                'similarity' => $similarity,
                'title' => $product->title
            ];
        }
        
        // Sort by similarity (highest first)
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        $topProducts = [];
        
        // Include strong matches (similarity > 0.8)
        foreach ($similarities as $item) {
            if ($item['similarity'] >= 0.8) {
                $topProducts[] = $item;
            }
        }
        
        // Calculate a dynamic threshold based on the highest similarity
        if (count($similarities) > 0) {
            $highestSimilarity = $similarities[0]['similarity'];
            // More inclusive threshold: lower minimum (0.4) and bigger gap (0.25)
            $dynamicThreshold = max(0.4, $highestSimilarity - 0.25); 
            
            // Add any products above the dynamic threshold that weren't already added
            foreach ($similarities as $item) {
                if ($item['similarity'] >= $dynamicThreshold && 
                    !in_array($item['id'], array_column($topProducts, 'id'))) {
                    $topProducts[] = $item;
                }
            }
        }
        
        // If we don't have at least 5 products yet, add more from the sorted list
        if (count($topProducts) < 5 && count($similarities) > 0) {
            $additionalProductsNeeded = 5 - count($topProducts);
            $existingIds = array_column($topProducts, 'id');
            
            // Find products not already included
            $remainingProducts = array_filter($similarities, function($item) use ($existingIds) {
                return !in_array($item['id'], $existingIds);
            });
            
            // Take the top N remaining products
            $additionalProducts = array_slice($remainingProducts, 0, $additionalProductsNeeded);
            
            // Add these to our results
            foreach ($additionalProducts as $product) {
                $topProducts[] = $product;
            }
        }
        
        // Cap at 10 max products
        $topProducts = array_slice($topProducts, 0, 5);
        
        return $topProducts;
    }
} 