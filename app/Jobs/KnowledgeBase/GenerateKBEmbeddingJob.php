<?php

namespace App\Jobs\KnowledgeBase;

use App\Models\KnowledgeBase;
use App\Services\Rag\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use OpenAI;

class GenerateKBEmbeddingJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    
    private KnowledgeBase $context;
    private $similarityThreshold = 0.8; // Threshold for considering contexts too similar

    /**
     * Create a new job instance.
     */
    public function __construct(KnowledgeBase $context)
    {
        $this->context = $context;
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

        $text = $this->context->getTextForEmbedding();
        
        try {
            $openai = OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->withOrganization(config('services.openai.organization'))
                ->make();
            
            $response = $openai->embeddings()->create([
                'model' => 'text-embedding-3-large',
                'input' => $text
            ]);
            
            // Update context with embedding
            $this->context->embedding = json_encode($response->embeddings[0]->embedding);
            $this->context->embedding_processed_at = now();
            $this->context->timestamps = false;
            $this->context->save();
            
            
            // After embedding is created, check for similar contexts
            $this->checkForSimilarContexts($embeddingService);
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Check if this context is too similar to other existing contexts
     * and delete it if similarity is above threshold
     */
    private function checkForSimilarContexts(EmbeddingService $embeddingService): void
    {
        // Get workspace ID of this context
        $workspaceId = $this->context->workspace_id;
        
        // Skip new contexts without workspace
        if (!$workspaceId) {
            return;
        }
        
        // Get all other contexts in the same workspace that already have embeddings
        // excluding the current context
        $otherContexts = KnowledgeBase::where('workspace_id', $workspaceId)
            ->where('id', '!=', $this->context->id)
            ->whereNotNull('embedding')
            ->whereNotNull('embedding_processed_at')
            ->get();
        
        // Skip if no other contexts to compare with
        if ($otherContexts->isEmpty()) {
            // No other contexts to compare with, so this is completely unique
            $this->context->similarity_score = 0;
            $this->context->timestamps = false;
            $this->context->save();
            return;
        }
        
        // Get the current context embedding
        $currentEmbedding = json_decode($this->context->embedding, true);
        if (empty($currentEmbedding)) {
            return;
        }
        
        $highestSimilarity = 0;
        $mostSimilarContextId = null;
        $mostSimilarContext = null;
        $totalSimilarity = 0;
        $validComparisons = 0;
        
        // Check similarity with each existing context
        foreach ($otherContexts as $otherContext) {
            $otherEmbedding = json_decode($otherContext->embedding, true);
            if (empty($otherEmbedding)) {
                continue;
            }
            
            // Calculate similarity
            $similarity = $embeddingService->calculateCosineSimilarity($currentEmbedding, $otherEmbedding);
            
            // Add to total for average calculation
            $totalSimilarity += $similarity;
            $validComparisons++;
            
            // Track highest similarity for deletion decision
            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $mostSimilarContextId = $otherContext->id;
                $mostSimilarContext = $otherContext;
            }
        }
        
        // Calculate average similarity
        $averageSimilarity = $validComparisons > 0 ? $totalSimilarity / $validComparisons : 0;
        
        // Save the average similarity score to the database
        // This score represents how similar this context is to others on average
        // Higher score = less unique, Lower score = more unique
        $this->context->similarity_score = $averageSimilarity;
        $this->context->timestamps = false;
        $this->context->save();
        
        Log::info("Updated average similarity score for context", [
            'context_id' => $this->context->id,
            'avg_similarity_score' => $averageSimilarity,
            'highest_similarity' => $highestSimilarity,
            'most_similar_to' => $mostSimilarContextId,
            'comparisons' => $validComparisons,
            'workspace_id' => $workspaceId
        ]);
        
        // If highest similarity is above threshold, delete this context
        // Note: We still use highest similarity for deletion decision
        if ($highestSimilarity >= $this->similarityThreshold) {
            Log::info("Deleting similar context", [
                'deleted_context_id' => $this->context->id,
                'similar_to_context_id' => $mostSimilarContextId,
                'highest_similarity' => $highestSimilarity,
                'avg_similarity' => $averageSimilarity,
                'deleted_question' => $this->context->question,
                'similar_question' => $mostSimilarContext->question,
                'workspace_id' => $workspaceId
            ]);
            
            // Delete the current context since it's too similar to an existing one
            $this->context->delete();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateEmbeddingJob failed for context #{$this->context->id}: " . $exception->getMessage(), [
            'batch_id' => $this->batch() ? $this->batch()->id : null
        ]);
    }
} 