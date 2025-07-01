<?php

namespace App\Livewire\App\KnowledgeBase;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\ProductFeed;
use App\Models\Product;
use App\Enums\ProductFeedStatus;
use App\Services\ProductCatalog\ProductFeedProcessor;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Flux\Flux;

class ProductFeeds extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Locked]
    public Workspace $workspace;

    public string $search = '';
    
    // Form data
    public array $feedForm = [];
    
    // UI state
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingFeedId = null;
    public ?int $deletingFeedId = null;
    
    // Statistics
    public int $totalFeeds = 0;
    public int $activeFeeds = 0;
    public int $errorFeeds = 0;
    public int $totalProducts = 0;

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->firstOrFail();
            
        $this->updateStats();
        $this->resetFeedForm();
    }
    
    public function updateStats(): void
    {
        $this->totalFeeds = ProductFeed::where('workspace_id', $this->workspace->id)->count();
        $this->activeFeeds = ProductFeed::where('workspace_id', $this->workspace->id)
            ->where('status', ProductFeedStatus::IDLE)
            ->count();
        $this->errorFeeds = ProductFeed::where('workspace_id', $this->workspace->id)
            ->where('status', ProductFeedStatus::ERROR)
            ->count();
        $this->totalProducts = Product::where('workspace_id', $this->workspace->id)->count();
    }

    public function resetFeedForm(): void
    {
        $this->feedForm = [
            'name' => '',
            'url' => '',
            'provider' => 'shopify',
            'scan_frequency' => 10080, // Weekly in minutes
        ];
    }

    public function createFeed(): void
    {
        $this->resetFeedForm();
        $this->showCreateModal = true;
    }

    public function storeFeed(): void
    {
        $this->validate([
            'feedForm.name' => 'required|string|max:255',
            'feedForm.url' => 'required|url|unique:product_feeds,url,NULL,id,workspace_id,' . $this->workspace->id,
            'feedForm.provider' => 'required|string|in:shopify',
            'feedForm.scan_frequency' => 'required|integer|min:60|max:43200', // 1 hour to 30 days
        ]);

        $feed = ProductFeed::create([
            'workspace_id' => $this->workspace->id,
            'name' => $this->feedForm['name'],
            'url' => $this->feedForm['url'],
            'provider' => $this->feedForm['provider'],
            'scan_frequency' => $this->feedForm['scan_frequency'],
            'status' => ProductFeedStatus::PROCESSING, // Set to processing since we're auto-syncing
        ]);

        // Automatically process the feed after creation
        \App\Jobs\KnowledgeBase\ProcessProductFeed::dispatch($feed);

        $this->showCreateModal = false;
        $this->updateStats();
        Flux::toast(variant: 'success', text: 'Product feed created successfully and sync started. This may take a few minutes.');
    }

    public function editFeed($feedId): void
    {
        $feed = ProductFeed::where('workspace_id', $this->workspace->id)
            ->findOrFail($feedId);

        $this->editingFeedId = $feedId;
        $this->feedForm = [
            'name' => $feed->name,
            'url' => $feed->url,
            'provider' => $feed->provider,
            'scan_frequency' => $feed->scan_frequency,
        ];
        
        $this->showEditModal = true;
    }

    public function updateFeed(): void
    {
        $this->validate([
            'feedForm.name' => 'required|string|max:255',
            'feedForm.url' => 'required|url|unique:product_feeds,url,' . $this->editingFeedId . ',id,workspace_id,' . $this->workspace->id,
            'feedForm.provider' => 'required|string|in:shopify',
            'feedForm.scan_frequency' => 'required|integer|min:60|max:43200',
        ]);

        $feed = ProductFeed::where('workspace_id', $this->workspace->id)
            ->findOrFail($this->editingFeedId);

        $feed->update([
            'name' => $this->feedForm['name'],
            'url' => $this->feedForm['url'],
            'provider' => $this->feedForm['provider'],
            'scan_frequency' => $this->feedForm['scan_frequency'],
        ]);

        $this->showEditModal = false;
        $this->editingFeedId = null;
        $this->updateStats();
        Flux::toast(variant: 'success', text: 'Product feed updated successfully.');
    }

    public function confirmDelete($feedId): void
    {
        $this->deletingFeedId = $feedId;
        $this->showDeleteModal = true;
    }

    public function deleteFeed(): void
    {
        $feed = ProductFeed::where('workspace_id', $this->workspace->id)
            ->findOrFail($this->deletingFeedId);

        $feed->delete();

        $this->showDeleteModal = false;
        $this->deletingFeedId = null;
        $this->updateStats();
        Flux::toast(variant: 'success', text: 'Product feed and all associated products deleted successfully.');
    }

    public function processFeed($feedId): void
    {
        $feed = ProductFeed::where('workspace_id', $this->workspace->id)
            ->findOrFail($feedId);

        if ($feed->status === ProductFeedStatus::PROCESSING) {
            Flux::toast(variant: 'warning', text: 'Feed is already being processed.');
            return;
        }

        // Update status to processing
        $feed->update(['status' => ProductFeedStatus::PROCESSING]);

        // Dispatch job to process feed in background
        \App\Jobs\KnowledgeBase\ProcessProductFeed::dispatch($feed);

        Flux::toast(variant: 'success', text: 'Feed processing started. This may take a few minutes.');
    }

    public function testFeed($feedId): void
    {
        $feed = ProductFeed::where('workspace_id', $this->workspace->id)
            ->findOrFail($feedId);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($feed->url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (is_array($data) && !empty($data)) {
                    Flux::toast(variant: 'success', text: 'Feed URL is accessible and contains valid data.');
                } else {
                    Flux::toast(variant: 'warning', text: 'Feed URL is accessible but may not contain valid product data.');
                }
            } else {
                Flux::toast(variant: 'danger', text: 'Feed URL returned error: ' . $response->status());
            }
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: 'Failed to connect to feed URL: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $feeds = ProductFeed::query()
            ->withCount('products')
            ->where('workspace_id', $this->workspace->id)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('url', 'like', '%' . $this->search . '%')
                        ->orWhere('provider', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.app.knowledge-base.product-feeds', [
            'feeds' => $feeds,
        ])->extends('layouts.app')->section('main');
    }
} 