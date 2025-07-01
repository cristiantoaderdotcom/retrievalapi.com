<div>
    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="manual" icon="clipboard-document-list">Manual Entry</flux:tab>
            <flux:tab name="feeds" icon="cloud-arrow-down">Import from Feeds</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="manual">
            <flux:card class="space-y-6">
                <div class="mb-2">
                    <flux:heading level="3">Add New Product</flux:heading>
                    <p class="text-zinc-600">Manually add products to your chatbot</p>
                </div>
                
                <form wire:submit="store" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <flux:input wire:model="name" label="Product Name" placeholder="Enter product name" required />
                            
                            <flux:textarea wire:model="description" rows="4" label="Description" 
                                placeholder="Enter product description" description="Provide a detailed description of your product (optional)" />
                            
                            <div class="grid grid-cols-2 gap-4">
                                <flux:input wire:model="price" type="number" step="0.01" min="0" label="Price" placeholder="0.00" />
                                <flux:select wire:model="currency" label="Currency">
                                    <flux:select.option value="USD">USD ($)</flux:select.option>
                                    <flux:select.option value="EUR">EUR (€)</flux:select.option>
                                    <flux:select.option value="GBP">GBP (£)</flux:select.option>
                                    <flux:select.option value="JPY">JPY (¥)</flux:select.option>
                                    <flux:select.option value="CAD">CAD (C$)</flux:select.option>
                                    <flux:select.option value="AUD">AUD (A$)</flux:select.option>
                                </flux:select>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <flux:field>
                                <flux:label>Product Image</flux:label>
                                <flux:description>
                                    Upload an image of your product (optional)
                                </flux:description>

                                <flux:input id="productImage-{{ rand() }}" type="file" wire:model="productImage" label="Product Image"
                                    description-trailing="Max size: 5MB | Supported formats: jpg, png, webp"
                                    accept="image/*" />

                                @error('productImage')
                                    <flux:error message="{{ $message }}" />
                                @enderror

                                <div wire:loading.flex wire:target="productImage" class="mt-4 items-center gap-3 text-sm">
                                    <flux:icon.loading variant="micro" />
                                    Uploading...
                                </div>
                                
                                @if ($productImage)
                                    <div class="mt-4">
                                        <div class="text-sm font-medium text-zinc-500 mb-2">Preview:</div>
                                        <img src="{{ $productImage->temporaryUrl() }}" class="w-32 h-32 object-cover rounded-lg border border-zinc-200" alt="Preview">
                                    </div>
                                @endif
                            </flux:field>
                            
                            <flux:input wire:model="tags" label="Tags" 
                                placeholder="premium, limited, special" 
                                description="Separate tags with commas (optional)" />
                            
                            <flux:input wire:model="categories" label="Categories" 
                                placeholder="electronics, accessories, gadgets" 
                                description="Separate categories with commas (optional)" />
                        </div>
                    </div>
                    
                    <flux:button type="submit" variant="primary" icon-trailing="plus">Add Product</flux:button>
                </form>
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="feeds">
            <livewire:app.chatbot.resources.product-feeds :chatbot="$chatbot" />
        </flux:tab.panel>
    </flux:tab.group>

    <div class="mt-6">
        @include('livewire.app.chatbot.resources._partials.stats')
        @include('livewire.app.chatbot.resources._partials.products-table')
    </div>
</div> 