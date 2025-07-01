<?php

namespace App\Console\Commands;

use App\Enums\ProductFeedStatus;
use App\Models\ProductFeed;
use App\Services\ProductCatalog\ProductFeedProcessor;
use Illuminate\Console\Command;

class ProcessProductFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-catalog:process-feeds {--feed_id= : Process a specific feed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process product feeds that are due for processing';

    /**
     * The feed processor instance.
     *
     * @var ProductFeedProcessor
     */
    protected $processor;

    /**
     * Create a new command instance.
     */
    public function __construct(ProductFeedProcessor $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feedId = $this->option('feed_id');

        if ($feedId) {
            // Process a specific feed
            $feed = ProductFeed::find($feedId);
            
            if (!$feed) {
                $this->error("Feed with ID {$feedId} not found.");
                return 1;
            }

            $this->processProductFeed($feed);
        } else {
            // Process all feeds that are due
            $feeds = ProductFeed::where('status', '!=', ProductFeedStatus::PROCESSING)
                ->get();

            if ($feeds->isEmpty()) {
                $this->info('No product feeds to process.');
                return 0;
            }

            $processedCount = 0;
            $errorCount = 0;
            $skippedCount = 0;

            foreach ($feeds as $feed) {
                if (!$feed->isDueForProcessing()) {
                    $skippedCount++;
                    continue;
                }

                if ($this->processProductFeed($feed)) {
                    $processedCount++;
                } else {
                    $errorCount++;
                }
            }

            $this->info("Product feeds processed: {$processedCount}, errors: {$errorCount}, skipped: {$skippedCount}");
        }

        return 0;
    }

    /**
     * Process a product feed.
     *
     * @param ProductFeed $feed
     * @return bool
     */
    protected function processProductFeed(ProductFeed $feed): bool
    {
        $this->info("Processing feed: {$feed->name} (ID: {$feed->id})");

        $result = $this->processor->process($feed);

        if ($result) {
            $this->info("Feed processed successfully.");
        } else {
            $this->error("Error processing feed: {$feed->error_message}");
        }

        return $result;
    }
} 