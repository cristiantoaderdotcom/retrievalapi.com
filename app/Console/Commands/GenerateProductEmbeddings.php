<?php

namespace App\Console\Commands;

use App\Jobs\KnowledgeBase\GenerateProductEmbeddingJob;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class GenerateProductEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-embeddings 
                            {--limit=500 : Maximum number of products to process in a single run} 
                            {--batch : Use job batching to track progress}
                            {--workspace= : Process products for a specific workspace ID}
                            {--force : Regenerate embeddings even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to generate embeddings for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $useBatch = $this->option('batch');
        $workspaceId = $this->option('workspace');
        $force = $this->option('force');
        
        // Build query for products that need embeddings
        $query = Product::query();
        
        if ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        }
        
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('embedding_processed_at')
                  ->orWhereNull('embedding');
            });
        }
        
        // Get products without embeddings, limited to the specified amount
        $products = $query->limit($limit)->get();
        
        // Get total count for reporting
        $totalQuery = Product::query();
        if ($workspaceId) {
            $totalQuery->where('workspace_id', $workspaceId);
        }
        if (!$force) {
            $totalQuery->where(function ($q) {
                $q->whereNull('embedding_processed_at')
                  ->orWhereNull('embedding');
            });
        }
        $totalCount = $totalQuery->count();
            
        $remainingAfterThisRun = max(0, $totalCount - $products->count());
   
        if ($products->isEmpty()) {
            $this->info('No products need embedding generation.');
            return 0;
        }
        
        $this->info("Found {$products->count()} products to process (out of {$totalCount} total)");
        if ($remainingAfterThisRun > 0) {
            $this->info("After this run, {$remainingAfterThisRun} products will still need processing");
        }
        
        if ($useBatch) {
            // Using batch to track progress
            $jobs = $products->map(function ($product) {
                return new GenerateProductEmbeddingJob($product);
            })->toArray();
            
            $batch = Bus::batch($jobs)
                ->name('Generate Product Embeddings')
                ->onQueue('low')
                ->dispatch();
                
            $this->info("Dispatched batch with {$batch->totalJobs} jobs. Batch ID: {$batch->id}");

        } else {
            // Dispatching individual jobs
            $dispatchedCount = 0;
            $bar = $this->output->createProgressBar($products->count());
            $bar->start();
            
            // Dispatch a job for each product
            foreach ($products as $product) {
                GenerateProductEmbeddingJob::dispatch($product)->onQueue('low');
                $dispatchedCount++;
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Dispatched {$dispatchedCount} individual jobs to the 'low' queue");
        }
        
        return 0;
    }
} 