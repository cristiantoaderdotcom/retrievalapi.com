<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use App\Jobs\ProcessProductFeedImport;
use App\Models\Chatbot\Chatbot;
use App\Models\Chatbot\ChatbotProductFeed;
use App\Services\ProductFeeds\ProductFeedRegistry;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ProductFeeds extends Component
{
    use WithPagination;
    
    #[Locked]
    public Chatbot $chatbot;
    
    // Form properties
    public string $name = '';
    public string $platform = 'shopify'; // Default to Shopify
    public string $feed_url = '';
    public array $credentials = [];
    public array $configuration = [];
    
    // Modal states
    public bool $showCreateModal = false;
    public bool $showDeleteModal = false;
    public bool $showCredentialsFields = false;
    public ?int $deletingFeedId = null;
    
    // Current feed adapter info
    public array $currentAdapterFields = [];
    
    public function mount($chatbot): void
    {
        $this->chatbot = $chatbot;
        $this->updateAdapterFields();
    }
    
    public function render(): View
    {
        $feeds = ChatbotProductFeed::where('chatbot_id', $this->chatbot->id)
            ->orderByDesc('created_at')
            ->paginate(10);
            
        $adapters = ProductFeedRegistry::getAdapterInfo();
        
        return view('livewire.app.chatbot.resources.product-feeds', [
            'feeds' => $feeds,
            'adapters' => $adapters,
        ]);
    }
    
    public function updatedPlatform(): void
    {
        $this->credentials = [];
        $this->configuration = [];
        $this->updateAdapterFields();
    }
    
    public function updateAdapterFields(): void
    {
        $adapter = ProductFeedRegistry::getAdapter($this->platform);
        
        if ($adapter) {
            $this->currentAdapterFields = $adapter->getConfigFields();
        } else {
            $this->currentAdapterFields = [];
        }
    }
    
    public function create(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function store(): void
    {
        $adapter = ProductFeedRegistry::getAdapter($this->platform);
        
        if (!$adapter) {
            $this->addError('platform', 'Unsupported platform');
            return;
        }
        
        $fieldsValidation = [];
        $fieldsValidation['name'] = 'required|string|max:255';
        
        // Add validation rules for each config field
        foreach ($this->currentAdapterFields as $field) {
            $type = $field['type'];
            $name = $field['name'];
            $required = !empty($field['required']);
            
            $propertyType = in_array($name, ['shop_domain', 'api_key', 'access_token']) 
                ? 'credentials.' . $name 
                : 'configuration.' . $name;
            
            $rule = $required ? 'required|' : 'nullable|';
            
            switch ($type) {
                case 'number':
                    $rule .= 'numeric';
                    break;
                case 'url':
                    $rule .= 'url';
                    break;
                default:
                    $rule .= 'string';
                    break;
            }
            
            $fieldsValidation[$propertyType] = $rule;
        }
        
        $this->validate($fieldsValidation);
        
        $feed = ChatbotProductFeed::create([
            'chatbot_id' => $this->chatbot->id,
            'name' => $this->name,
            'platform' => $this->platform,
            'feed_url' => $this->feed_url,
            'credentials' => $this->credentials,
            'configuration' => $this->configuration,
            'sync_status' => 'pending',
        ]);
        
        // Dispatch the job to process the feed
        ProcessProductFeedImport::dispatch($feed);
        
        $this->showCreateModal = false;
        Flux::toast(variant: 'success', text: 'Product feed created successfully');
        $this->resetForm();
    }
    
    public function syncFeed(int $feedId): void
    {
        $feed = ChatbotProductFeed::findOrFail($feedId);
        
        $feed->update([
            'sync_status' => 'pending',
            'sync_error' => null,
        ]);
        
        ProcessProductFeedImport::dispatch($feed);
        
        Flux::toast(variant: 'success', text: 'Product feed sync started');
    }
    
    public function confirmDelete(int $feedId): void
    {
        $this->deletingFeedId = $feedId;
        $this->showDeleteModal = true;
    }
    
    public function delete(): void
    {
        if (!$this->deletingFeedId) {
            return;
        }
        
        $feed = ChatbotProductFeed::findOrFail($this->deletingFeedId);
        $feed->delete();
        
        $this->showDeleteModal = false;
        $this->deletingFeedId = null;
        
        Flux::toast(variant: 'success', text: 'Product feed deleted successfully');
    }
    
    public function resetForm(): void
    {
        $this->name = '';
        $this->platform = 'shopify';
        $this->feed_url = '';
        $this->credentials = [];
        $this->configuration = [];
        $this->updateAdapterFields();
        
        $this->resetErrorBag();
    }
    
    public function cancel(): void
    {
        $this->showCreateModal = false;
        $this->showDeleteModal = false;
        $this->deletingFeedId = null;
        $this->resetForm();
    }
}
