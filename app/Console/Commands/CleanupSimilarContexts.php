<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use App\Models\Workspace;
use App\Services\Rag\EmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupSimilarContexts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knowledgebase:cleanup-similar 
                           {workspace_id? : The ID of the workspace to clean up (if not provided, all workspaces will be processed)} 
                           {--similarity=0.9 : Threshold for considering contexts too similar (0.0 to 1.0)}
                           {--dry-run : Only show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identify and clean up similar contexts in the knowledge base';

    /**
     * The embedding service
     */
    protected EmbeddingService $embeddingService;

    /**
     * Create a new command instance.
     */
    public function __construct(EmbeddingService $embeddingService)
    {
        parent::__construct();
        $this->embeddingService = $embeddingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workspaceId = $this->argument('workspace_id');
        $similarityThreshold = $this->option('similarity');
        $dryRun = $this->option('dry-run');

        $this->info("Starting similar context cleanup" . ($dryRun ? ' (DRY RUN)' : ''));
        $this->info("Similarity threshold: " . $similarityThreshold);

        // Get workspaces to process
        $workspaces = $workspaceId 
            ? Workspace::where('id', $workspaceId)->get() 
            : Workspace::all();

        if ($workspaces->isEmpty()) {
            $this->error("No workspaces found to process");
            return 1;
        }

        $totalDeleted = 0;

        // Process each workspace
        foreach ($workspaces as $workspace) {
            $this->info("\nProcessing workspace: {$workspace->name} (ID: {$workspace->id})");

            // Get all contexts with embeddings for this workspace
            $contexts = KnowledgeBase::where('workspace_id', $workspace->id)
                ->whereNotNull('embedding')
                ->whereNotNull('embedding_processed_at')
                ->get();

            if ($contexts->isEmpty()) {
                $this->warn("  No contexts with embeddings found in this workspace");
                continue;
            }

            $this->info("  Found {$contexts->count()} contexts with embeddings");

            // Keep track of contexts to delete
            $contextsToDelete = [];
            $processedCount = 0;

            // Set up progress bar
            $bar = $this->output->createProgressBar($contexts->count());
            $bar->start();

            // Compare each context with all others
            foreach ($contexts as $context) {
                // Skip if this context is already marked for deletion
                if (in_array($context->id, $contextsToDelete)) {
                    $bar->advance();
                    continue;
                }

                $currentEmbedding = json_decode($context->embedding, true);
                if (empty($currentEmbedding)) {
                    $bar->advance();
                    continue;
                }

                $highestSimilarity = 0;
                $mostSimilarContextId = null;
                $totalSimilarity = 0;
                $validComparisons = 0;
                
                // Compare with all other contexts not yet processed
                foreach ($contexts as $otherContext) {
                    // Skip self or already processed contexts or contexts marked for deletion
                    if ($otherContext->id === $context->id || 
                        in_array($otherContext->id, $contextsToDelete)) {
                        continue;
                    }

                    $otherEmbedding = json_decode($otherContext->embedding, true);
                    if (empty($otherEmbedding)) {
                        continue;
                    }

                    // Calculate similarity
                    $similarity = $this->embeddingService->calculateCosineSimilarity(
                        $currentEmbedding, 
                        $otherEmbedding
                    );
                    
                    // Add to total for average calculation
                    $totalSimilarity += $similarity;
                    $validComparisons++;
                    
                    // Track highest similarity
                    if ($similarity > $highestSimilarity) {
                        $highestSimilarity = $similarity;
                        $mostSimilarContextId = $otherContext->id;
                    }

                    // If similarity is above threshold, mark for deletion
                    if ($similarity >= $similarityThreshold) {
                        $this->comment("\n  Found similar contexts:");
                        $this->line("    - Context #{$context->id}: {$context->question}");
                        $this->line("    - Context #{$otherContext->id}: {$otherContext->question}");
                        $this->line("    - Similarity: " . number_format($similarity, 4));

                        // Always keep the older context and delete the newer one
                        $contextToKeep = $context->created_at <= $otherContext->created_at ? $context : $otherContext;
                        $contextToDelete = $context->created_at <= $otherContext->created_at ? $otherContext : $context;

                        $this->info("    - Keeping: #{$contextToKeep->id} (created: {$contextToKeep->created_at})");
                        $this->info("    - " . ($dryRun ? "Would delete" : "Deleting") . ": #{$contextToDelete->id} (created: {$contextToDelete->created_at})");

                        $contextsToDelete[] = $contextToDelete->id;
                    }
                }
                
                // Calculate average similarity
                $averageSimilarity = $validComparisons > 0 ? $totalSimilarity / $validComparisons : 0;
                
                // Update similarity score for this context if we're not going to delete it
                if (!in_array($context->id, $contextsToDelete)) {
                    // Update similarity score in database
                    $context->similarity_score = $averageSimilarity;
                    $context->timestamps = false;
                    $context->save();
                    
                    if ($validComparisons > 0) {
                        $this->line("\n  Updated average similarity score for context #{$context->id}: " . 
                            number_format($averageSimilarity, 4) . 
                            " (compared with {$validComparisons} contexts)" .
                            ($mostSimilarContextId ? ", most similar to #{$mostSimilarContextId} (" . number_format($highestSimilarity, 4) . ")" : ""));
                    }
                }

                $processedCount++;
                $bar->advance();
            }

            $bar->finish();

            // Delete the marked contexts if not in dry run mode
            if (!$dryRun && !empty($contextsToDelete)) {
                $deleted = KnowledgeBase::whereIn('id', $contextsToDelete)->delete();
                $this->info("\n  Deleted {$deleted} similar contexts");
                $totalDeleted += $deleted;

                Log::info("Deleted similar contexts in workspace", [
                    'workspace_id' => $workspace->id,
                    'deleted_count' => $deleted,
                    'deleted_context_ids' => $contextsToDelete
                ]);
            } else {
                $this->info("\n  Would delete " . count(array_unique($contextsToDelete)) . " similar contexts");
                $totalDeleted += count(array_unique($contextsToDelete));
            }
        }

        $this->info("\nCleanup complete! " . ($dryRun ? "Would have deleted" : "Deleted") . " {$totalDeleted} similar contexts across " . $workspaces->count() . " workspaces");

        return 0;
    }
} 