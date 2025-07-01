<?php

namespace App\Livewire\App\KnowledgeBase;

use Livewire\Component;
use App\Models\Workspace;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductOption;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Flux\Flux;

class ManageProduct extends Component
{
    use WithFileUploads;

    #[Locked]
    public Workspace $workspace;
    
   
    public $product_id;
    // Product form data
    public array $productForm = [];
    
    // Variants form data
    public array $variantForms = [];
    
    // Options form data
    public array $optionForms = [];
    
    // Images form data
    public array $imageForms = [];
    
    // UI state
    public string $activeTab = 'details';
    public bool $showAddVariant = false;
    public bool $showAddOption = false;
    public bool $showAddImage = false;

    #[Locked]
    public Product $product;

    public function mount($uuid, $product_id)
    {
        $this->workspace = Workspace::where('uuid', $uuid)->firstOrFail();
        $this->product = Product::with(['variants', 'options', 'images', 'feed'])
            ->where('workspace_id', $this->workspace->id)
            ->where('id', $product_id)
            ->firstOrFail();
            
        $this->initializeForms();
    }
    
    protected function initializeForms(): void
    {
        // Initialize product form
        $this->productForm = [
            'title' => $this->product->title,
            'handle' => $this->product->handle,
            'body_html' => $this->product->body_html,
            'vendor' => $this->product->vendor,
            'product_type' => $this->product->product_type,
            'tags' => is_array($this->product->tags) ? implode(', ', array_filter($this->product->tags)) : ($this->product->tags ?? ''),
            'published_at' => $this->product->published_at?->format('Y-m-d\TH:i'),
        ];
        
        // Initialize variant forms
        foreach ($this->product->variants as $variant) {
            $this->variantForms[$variant->id] = [
                'title' => $variant->title,
                'option1' => $variant->option1,
                'option2' => $variant->option2,
                'option3' => $variant->option3,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'compare_at_price' => $variant->compare_at_price,
                'requires_shipping' => $variant->requires_shipping,
                'taxable' => $variant->taxable,
                'available' => $variant->available,
                'grams' => $variant->grams,
                'position' => $variant->position,
            ];
        }
        
        // Initialize option forms
        foreach ($this->product->options as $option) {
            $this->optionForms[$option->id] = [
                'name' => $option->name,
                'position' => $option->position,
                'values' => is_array($option->values) ? implode(', ', array_filter($option->values)) : ($option->values ?? ''),
            ];
        }
        
        // Initialize image forms
        foreach ($this->product->images as $image) {
            $this->imageForms[$image->id] = [
                'src' => $image->src,
                'position' => $image->position,
                'width' => $image->width,
                'height' => $image->height,
            ];
        }
    }

    public function updateProduct(): void
    {
        $this->validate([
            'productForm.title' => 'required|string|max:255',
            'productForm.handle' => 'nullable|string|max:255',
            'productForm.body_html' => 'nullable|string',
            'productForm.vendor' => 'nullable|string|max:255',
            'productForm.product_type' => 'nullable|string|max:255',
            'productForm.tags' => 'nullable|string',
            'productForm.published_at' => 'nullable|date',
        ]);

        $this->product->update([
            'title' => $this->productForm['title'],
            'handle' => $this->productForm['handle'],
            'body_html' => $this->productForm['body_html'],
            'vendor' => $this->productForm['vendor'],
            'product_type' => $this->productForm['product_type'],
            'tags' => !empty($this->productForm['tags']) ? array_map('trim', explode(',', $this->productForm['tags'])) : null,
            'published_at' => !empty($this->productForm['published_at']) ? $this->productForm['published_at'] : null,
            'embedding_processed_at' => null, // Reset embedding when product changes
        ]);

        Flux::toast(variant: 'success', text: 'Product updated successfully.');
    }

    public function updateVariant($variantId): void
    {
        $this->validate([
            "variantForms.{$variantId}.title" => 'required|string|max:255',
            "variantForms.{$variantId}.price" => 'nullable|numeric|min:0',
            "variantForms.{$variantId}.compare_at_price" => 'nullable|numeric|min:0',
            "variantForms.{$variantId}.sku" => 'nullable|string|max:255',
            "variantForms.{$variantId}.grams" => 'nullable|integer|min:0',
            "variantForms.{$variantId}.position" => 'nullable|integer|min:0',
        ]);

        $variant = ProductVariant::findOrFail($variantId);
        $variant->update($this->variantForms[$variantId]);

        Flux::toast(variant: 'success', text: 'Variant updated successfully.');
    }

    public function deleteVariant($variantId): void
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->delete();
        
        unset($this->variantForms[$variantId]);
        $this->product->refresh();

        Flux::toast(variant: 'success', text: 'Variant deleted successfully.');
    }

    public function addVariant(): void
    {
        $this->validate([
            'variantForms.new.title' => 'required|string|max:255',
            'variantForms.new.price' => 'nullable|numeric|min:0',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'title' => $this->variantForms['new']['title'],
            'price' => $this->variantForms['new']['price'] ?? null,
            'compare_at_price' => $this->variantForms['new']['compare_at_price'] ?? null,
            'sku' => $this->variantForms['new']['sku'] ?? null,
            'option1' => $this->variantForms['new']['option1'] ?? null,
            'option2' => $this->variantForms['new']['option2'] ?? null,
            'option3' => $this->variantForms['new']['option3'] ?? null,
            'requires_shipping' => $this->variantForms['new']['requires_shipping'] ?? true,
            'taxable' => $this->variantForms['new']['taxable'] ?? true,
            'available' => $this->variantForms['new']['available'] ?? true,
            'grams' => $this->variantForms['new']['grams'] ?? null,
            'position' => $this->variantForms['new']['position'] ?? 1,
        ]);

        $this->variantForms[$variant->id] = $this->variantForms['new'];
        unset($this->variantForms['new']);
        $this->showAddVariant = false;
        $this->product->refresh();

        Flux::toast(variant: 'success', text: 'Variant added successfully.');
    }

    public function updateOption($optionId): void
    {
        $this->validate([
            "optionForms.{$optionId}.name" => 'required|string|max:255',
            "optionForms.{$optionId}.values" => 'required|string',
        ]);

        $option = ProductOption::findOrFail($optionId);
        $option->update([
            'name' => $this->optionForms[$optionId]['name'],
            'position' => $this->optionForms[$optionId]['position'],
            'values' => array_map('trim', explode(',', $this->optionForms[$optionId]['values'])),
        ]);

        Flux::toast(variant: 'success', text: 'Option updated successfully.');
    }

    public function deleteOption($optionId): void
    {
        $option = ProductOption::findOrFail($optionId);
        $option->delete();
        
        unset($this->optionForms[$optionId]);
        $this->product->refresh();

        Flux::toast(variant: 'success', text: 'Option deleted successfully.');
    }

    public function updateImage($imageId): void
    {
        $this->validate([
            "imageForms.{$imageId}.src" => 'required|url',
            "imageForms.{$imageId}.position" => 'nullable|integer|min:0',
        ]);

        $image = ProductImage::findOrFail($imageId);
        $image->update($this->imageForms[$imageId]);

        Flux::toast(variant: 'success', text: 'Image updated successfully.');
    }

    public function deleteImage($imageId): void
    {
        $image = ProductImage::findOrFail($imageId);
        $image->delete();
        
        unset($this->imageForms[$imageId]);
        $this->product->refresh();

        Flux::toast(variant: 'success', text: 'Image deleted successfully.');
    }

    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.app.knowledge-base.manage-product')
            ->extends('layouts.app')
            ->section('main');
    }
} 