<?php

namespace App\Livewire\App\KnowledgeBase;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\Product;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class ProductCatalog extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Locked]
    public Workspace $workspace;

    public string $search = '';
    
    public int $totalProducts = 0;
    public int $publishedProducts = 0;
    public int $totalVariants = 0;

    public function mount($uuid)
    {
        $this->workspace = Workspace::query()
            ->where('uuid', $uuid)
            ->firstOrFail();
            
        $this->updateStats();
    }
    
    public function updateStats(): void
    {
        $this->totalProducts = Product::where('workspace_id', $this->workspace->id)->count();
        $this->publishedProducts = Product::where('workspace_id', $this->workspace->id)
            ->whereNotNull('published_at')
            ->count();
        $this->totalVariants = $this->workspace->products()
            ->withCount('variants')
            ->get()
            ->sum('variants_count');
    }

    public function manageProduct($productId)
    {
        return $this->redirect(route('app.workspace.knowledge-base.manage-product', [
            'uuid' => $this->workspace->uuid,
            'product_id' => $productId
        ]));
    }

    public function render()
    {
        $products = Product::query()
            ->with(['images', 'variants'])
            ->where('workspace_id', $this->workspace->id)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('vendor', 'like', '%' . $this->search . '%')
                        ->orWhere('product_type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.app.knowledge-base.product-catalog', [
            'products' => $products,
        ])->extends('layouts.app')->section('main');
    }
}
