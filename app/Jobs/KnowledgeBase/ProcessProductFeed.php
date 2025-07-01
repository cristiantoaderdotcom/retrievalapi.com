<?php

namespace App\Jobs\KnowledgeBase;

use App\Models\ProductFeed;
use App\Services\ProductCatalog\ProductFeedProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProductFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ProductFeed $feed
    ) {}

    public function handle(ProductFeedProcessor $processor): void
    {
        Log::info("Processing product feed: {$this->feed->name} (ID: {$this->feed->id})");
        
        $result = $processor->process($this->feed);
        
        if ($result) {
            Log::info("Successfully processed product feed: {$this->feed->name}");
        } else {
            Log::error("Failed to process product feed: {$this->feed->name}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Product feed processing job failed for feed {$this->feed->id}: " . $exception->getMessage(), [
            'feed_id' => $this->feed->id,
            'exception' => $exception,
        ]);
        
        // Update feed status to error
        $this->feed->update([
            'status' => \App\Enums\ProductFeedStatus::ERROR,
            'error_message' => $exception->getMessage(),
        ]);
    }
} 