<?php

namespace App\Jobs\KnowledgeBase;

use App\Enums\ResourceStatus;
use App\Jobs\TrainSource;
use App\Models\KnowledgeBaseResource;
use App\Models\KnowledgeBaseUrlResource;
use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTopUrlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;
    
    private Workspace $workspace;
    private int $maxUrlsToProcess;

    /**
     * Create a new job instance.
     */
    public function __construct(Workspace $workspace, int $maxUrlsToProcess = 50)
    {
        $this->workspace = $workspace;
        $this->maxUrlsToProcess = $maxUrlsToProcess;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find resources that have been prioritized but not yet processed
        $topResources = KnowledgeBaseResource::query()
            ->whereHasMorph('resourceable', [KnowledgeBaseUrlResource::class], function($query) {
                $query->whereNotNull('priority_score')
                      ->where('workspace_id', $this->workspace->id);
            })
            ->whereNull('status')
            ->join('knowledge_base_url_resources', function ($join) {
                $join->on('knowledge_base_url_resources.id', '=', 'knowledge_base_resources.resourceable_id')
                    ->where('knowledge_base_resources.resourceable_type', '=', KnowledgeBaseUrlResource::class)
                    ->where('knowledge_base_url_resources.workspace_id', '=', $this->workspace->id);
            })
            ->where('knowledge_base_resources.workspace_id', $this->workspace->id)
            ->orderByDesc('knowledge_base_url_resources.priority_score')
            ->orderByDesc('knowledge_base_url_resources.is_primary')
            ->select('knowledge_base_resources.*')
            ->take($this->maxUrlsToProcess)
            ->get();
        
        Log::info("Processing top {$topResources->count()} URLs by priority for workspace {$this->workspace->id}");
        
        // Process each resource
        foreach ($topResources as $resource) {
            // Update status and start processing
            $resource->update([
                'status' => ResourceStatus::PROCESSING,
                'process_started_at' => now(),
                'process_completed_at' => null,
            ]);
            
            // Dispatch the training job
            TrainSource::dispatch($resource);
            
            // Small delay to avoid overwhelming the queue
            usleep(100000); // 0.1 second
        }
        
        Log::info("Dispatched training jobs for top {$topResources->count()} resources");
    }
} 