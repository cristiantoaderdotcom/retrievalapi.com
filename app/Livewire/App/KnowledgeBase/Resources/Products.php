<?php

namespace App\Livewire\App\KnowledgeBase\Resources;

use App\Enums\ChatbotResourceStatus;
use App\Models\Chatbot\ChatbotProductResource;
use App\Models\Chatbot\ChatbotResource;
use App\Models\Chatbot\Chatbot;
use App\Traits\Livewire\App\Chatbot\HasResources;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Products extends Component {
    use WithPagination, WithoutUrlPagination, WithFileUploads;
    use HasResources;

    public string $name = '';
    public string $description = '';
    public $price = null;
    public string $currency = 'USD';
    public $productImage = null;
    public string $tags = '';
    public string $categories = '';

    public bool $polling = false;
    private bool $locked = false;

    #[Locked]
    public Chatbot $chatbot;

    public function mount($chatbot): void {
        $this->chatbot = $chatbot;
        $this->statuses = ChatbotResourceStatus::toArray();
    }

    public function render(Request $request): View {
        $resources = $this->chatbot
            ->resources()
            ->withWhereHas('resourceable', function ($query) {
                $query->when($this->filters['match'] && $this->filters['search'], function ($query) {
                    $match = $this->filters['match'];
                    $search = $this->filters['search'];
                    if ($match === 'contains') {
                        $query->where('name', 'like', '%' . $search . '%');
                    } elseif ($match === 'not_contains') {
                        $query->where('name', 'not like', '%' . $search . '%');
                    } elseif ($match === 'starts') {
                        $query->where('name', 'like', $search . '%');
                    } elseif ($match === 'ends') {
                        $query->where('name', 'like', '%' . $search);
                    }
                });
            })
            ->whereHasMorph('resourceable', [ChatbotProductResource::class])
            ->when(!empty($this->filters['status']), function ($query) {
                $query->where('status', $this->filters['status']);
            }, function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhereNot('status', ChatbotResourceStatus::HIDDEN);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $this->processing = $this->chatbot
            ->resources()
            ->where(function ($query) {
                $query->whereNot('status', ChatbotResourceStatus::FAILED)
                    ->whereNotNull('process_started_at')
                    ->whereNull('process_completed_at');
            })
            ->count();

        $this->processed = $this->chatbot
            ->resources()
            ->where('status', ChatbotResourceStatus::PROCESSED)
            ->count();

        $this->failed = $this->chatbot
            ->resources()
            ->where('status', ChatbotResourceStatus::FAILED)
            ->count();

        $this->polling = !empty($this->processing);

        if (empty($this->processed)) {
            $this->dispatch('lock-steps', true);
        } else if (!$this->polling) {
            $this->dispatch('lock-steps', false);
        }

        if ($this->locked !== $this->polling) {
            $this->locked = $this->polling;
            $this->dispatch('lock-steps', true);
        }

        return view('livewire.app.chatbot.resources.products', compact('resources'));
    }

    public function updatedProductImage(): void {
        $this->validate([
            'productImage' => 'image|max:5120', // 5MB max
        ]);
    }

    public function store(): void {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'productImage' => 'nullable|image|max:5120', // 5MB max
            'tags' => 'nullable|string',
            'categories' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                $imagePath = null;

                if ($this->productImage) {
                    $path = $this->productImage->store('uploads/users/' . auth()->id() . '/chatbot/' . $this->chatbot->id . '/products', 'public');
                    $imagePath = "storage/$path";
                }

                // Process tags and categories
                $tagsArray = $this->tags ? array_map('trim', explode(',', $this->tags)) : null;
                $categoriesArray = $this->categories ? array_map('trim', explode(',', $this->categories)) : null;

                $productResource = ChatbotProductResource::query()
                    ->create([
                        'name' => $this->name,
                        'description' => $this->description,
                        'price' => $this->price,
                        'currency' => $this->currency,
                        'image_path' => $imagePath,
                        'tags' => $tagsArray,
                        'categories' => $categoriesArray,
                    ]);

                $resource = new ChatbotResource([
                    'chatbot_id' => $this->chatbot->id,
                ]);

                $productResource->resource()->save($resource);
            });

            // Reset form after successful submission
            $this->reset(['name', 'description', 'price', 'productImage', 'tags', 'categories']);
            $this->currency = 'USD'; // Reset to default currency

        } catch (Exception $e) {
            Log::error('Failed to store product', ['error' => $e->getMessage()]);
            $this->addError('product', 'Failed to store product. Please try again.');
        }
    }
}
