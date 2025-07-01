<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Log;
use OpenAI;

class EmbeddingService
{
    /**
     * Generate an embedding for the given text.
     *
     * @param string $text
     * @return array
     * @throws \Exception
     */
    public function generateEmbedding(string $text): array
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
            
            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('Error generating embedding: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Find similar products based on a query text.
     *
     * @param string $query
     * @param int $workspaceId
     * @param int $limit
     * @param float $threshold
     * @return array
     */
    public function findSimilarProducts(string $query, int $workspaceId, int $limit = 5, float $threshold = 0.7): array
    {
        try {
            // Generate embedding for the query
            $queryEmbedding = $this->generateEmbedding($query);
            
            // Get products for the workspace
            $products = \App\Models\Product::where('workspace_id', $workspaceId)
                ->whereNotNull('embedding')
                ->get();
                
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
                $similarity = $this->calculateCosineSimilarity($queryEmbedding, $productEmbedding);
                
                // Store all similarities for threshold calculation
                $similarities[] = [
                    'id' => $product->id,
                    'similarity' => $similarity,
                    'title' => $product->title,
                    'handle' => $product->handle,
                    'body_html' => $product->body_html,
                    'vendor' => $product->vendor,
                    'product_type' => $product->product_type,
                    'distance' => 1 - $similarity // Convert similarity to distance
                ];
            }
            
            // Sort by similarity (highest first)
            usort($similarities, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            // Apply threshold filter
            $filteredResults = array_filter($similarities, function($result) use ($threshold) {
                return $result['similarity'] >= $threshold;
            });
            
            // Cap at specified limit
            return array_slice($filteredResults, 0, $limit);
            
        } catch (\Exception $e) {
            Log::error('Error finding similar products: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
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
} 