<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use App\Models\Workspace;
use Illuminate\Console\Command;

class AnalyzeKnowledgeBaseUniqueness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knowledgebase:analyze-uniqueness 
                           {workspace_id? : The ID of the workspace to analyze (if not provided, all workspaces will be processed)} 
                           {--threshold=0.7 : Threshold for considering contexts as not unique enough}
                           {--limit=20 : Number of least unique questions to display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze the uniqueness of knowledge base contexts based on similarity scores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workspaceId = $this->argument('workspace_id');
        $threshold = $this->option('threshold');
        $limit = $this->option('limit');

        $this->info("Analyzing knowledge base uniqueness");
        $this->info("Similarity threshold: {$threshold} (contexts with average score above this are considered less unique)");

        // Get workspaces to process
        $workspaces = $workspaceId 
            ? Workspace::where('id', $workspaceId)->get() 
            : Workspace::all();

        if ($workspaces->isEmpty()) {
            $this->error("No workspaces found to analyze");
            return 1;
        }

        foreach ($workspaces as $workspace) {
            $this->info("\nAnalyzing workspace: {$workspace->name} (ID: {$workspace->id})");

            // Get all contexts with embeddings and similarity scores
            $contexts = KnowledgeBase::where('workspace_id', $workspace->id)
                ->whereNotNull('embedding')
                ->whereNotNull('embedding_processed_at')
                ->get();

            if ($contexts->isEmpty()) {
                $this->warn("  No contexts with embeddings found in this workspace");
                continue;
            }

            $totalContexts = $contexts->count();
            $contextsWithoutScores = $contexts->whereNull('similarity_score')->count();
            $contextsWithScores = $totalContexts - $contextsWithoutScores;
            
            $this->info("  Found {$totalContexts} contexts with embeddings");
            
            if ($contextsWithoutScores > 0) {
                $this->warn("  {$contextsWithoutScores} contexts don't have similarity scores yet");
                $this->line("  Run 'php artisan knowledgebase:cleanup-similar --dry-run' to calculate scores without deleting");
            }
            
            if ($contextsWithScores == 0) {
                continue;
            }
            
            // Similarity score statistics
            $avgSimilarity = $contexts->whereNotNull('similarity_score')->avg('similarity_score');
            $maxSimilarity = $contexts->whereNotNull('similarity_score')->max('similarity_score');
            $minSimilarity = $contexts->whereNotNull('similarity_score')->min('similarity_score');
            
            $notUniqueEnough = $contexts->where('similarity_score', '>=', $threshold)->count();
            $percentNotUnique = ($notUniqueEnough / $contextsWithScores) * 100;
            
            $this->info("  Similarity Statistics:");
            $this->line("    - Average similarity across all contexts: " . number_format($avgSimilarity, 4));
            $this->line("    - Highest average similarity: " . number_format($maxSimilarity, 4));
            $this->line("    - Lowest average similarity: " . number_format($minSimilarity, 4));
            $this->line("    - Contexts with avg similarity >= {$threshold}: {$notUniqueEnough} (" . number_format($percentNotUnique, 1) . "%)");
            
            // Display least unique contexts
            $leastUnique = $contexts->whereNotNull('similarity_score')
                ->sortByDesc('similarity_score')
                ->take($limit);
                
            if ($leastUnique->isNotEmpty()) {
                $this->info("\n  Top {$limit} Least Unique Questions (highest avg similarity):");
                $this->line("  " . str_repeat('-', 100));
                $this->line("  | " . str_pad("ID", 6) . " | " . str_pad("Similarity", 10) . " | Question");
                $this->line("  " . str_repeat('-', 100));
                
                foreach ($leastUnique as $context) {
                    $question = mb_strlen($context->question) > 70 
                        ? mb_substr($context->question, 0, 67) . "..." 
                        : $context->question;
                        
                    $this->line("  | " . 
                        str_pad($context->id, 6) . " | " . 
                        str_pad(number_format($context->similarity_score, 4), 10) . " | " . 
                        $question);
                }
            }
            
            // Display most unique contexts
            $mostUnique = $contexts->whereNotNull('similarity_score')
                ->sortBy('similarity_score')
                ->take($limit);
                
            if ($mostUnique->isNotEmpty()) {
                $this->info("\n  Top {$limit} Most Unique Questions (lowest avg similarity):");
                $this->line("  " . str_repeat('-', 100));
                $this->line("  | " . str_pad("ID", 6) . " | " . str_pad("Similarity", 10) . " | Question");
                $this->line("  " . str_repeat('-', 100));
                
                foreach ($mostUnique as $context) {
                    $question = mb_strlen($context->question) > 70 
                        ? mb_substr($context->question, 0, 67) . "..." 
                        : $context->question;
                        
                    $this->line("  | " . 
                        str_pad($context->id, 6) . " | " . 
                        str_pad(number_format($context->similarity_score, 4), 10) . " | " . 
                        $question);
                }
            }
        }

        $this->info("\nAnalysis complete!");
        return 0;
    }
} 