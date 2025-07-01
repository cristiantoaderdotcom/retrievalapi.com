<?php

namespace App\Console\Commands;

use App\Jobs\KnowledgeBase\GenerateKBEmbeddingJob;
use App\Models\KnowledgeBase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus; 
use Illuminate\Support\Facades\Log;

class GenerateKBEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'knowledgebase:generate-embeddings {--limit=500 : Maximum number of contexts to process in a single run} {--batch : Use job batching to track progress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to generate embeddings for knowledgebase contexts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $useBatch = $this->option('batch');
        
        // Get contexts without embeddings, limited to the specified amount
        $contexts = KnowledgeBase::whereNull('embedding_processed_at')
            ->orWhereNull('embedding')
            ->limit($limit)
            ->get();
        
        $totalCount = KnowledgeBase::whereNull('embedding_processed_at')
            ->orWhereNull('embedding')
            ->count();
            
        $remainingAfterThisRun = max(0, $totalCount - $contexts->count());
   
        if ($contexts->isEmpty()) {
            return 0;
        }
        
        if ($useBatch) {
            // Using batch to track progress
            $jobs = $contexts->map(function ($context) {
                return new GenerateKBEmbeddingJob($context);
            })->toArray();
            
            $batch = Bus::batch($jobs)
                ->name('Generate KB Embeddings')
                ->onQueue('low')
                ->dispatch();           

        } else {
            // Dispatching individual jobs
            $dispatchedCount = 0;
            $bar = $this->output->createProgressBar($contexts->count());
            $bar->start();
            
            // Dispatch a job for each context
            foreach ($contexts as $context) {
                GenerateKBEmbeddingJob::dispatch($context)->onQueue('low');
                $dispatchedCount++;
                
                $bar->advance();
            }
            
            $bar->finish();
            
        }
        
        return 0;
    }
}
