<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <flux:button 
                        href="{{ route('app.workspace.knowledge-base.product-catalog', $workspace->uuid) }}"
                        wire:navigate
                        variant="ghost" 
                        icon="arrow-left" 
                        size="sm">
                        Back to Catalog
                    </flux:button>
                    <div class="h-6 w-px bg-zinc-300 dark:bg-zinc-600"></div>
                    <div>
                        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->title }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            @if($product->published_at)
                                <flux:badge color="green" variant="solid" size="xs">Published</flux:badge>
                            @else
                                <flux:badge color="gray" variant="solid" size="xs">Draft</flux:badge>
                            @endif
                            @if($product->vendor)
                                <span class="text-sm text-zinc-500">by {{ $product->vendor }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($product->feed)
                    <flux:badge variant="outline" size="sm">
                        <flux:icon name="rss" class="size-3 mr-1" />
                        {{ $product->feed->name }}
                    </flux:badge>
                @endif
                <flux:button wire:click="updateProduct" variant="primary" size="sm">
                    Save Changes
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <flux:tab.group>
        <flux:tabs variant="segmented" class="mb-6">
            <flux:tab 
                name="details" 
                icon="document-text" 
                wire:click="setActiveTab('details')"
                :current="$activeTab === 'details'">
                Product Details
            </flux:tab>
            <flux:tab 
                name="variants" 
                icon="squares-2x2" 
                wire:click="setActiveTab('variants')"
                :current="$activeTab === 'variants'">
                Variants ({{ $product->variants->count() }})
            </flux:tab>
            <flux:tab 
                name="options" 
                icon="adjustments-horizontal" 
                wire:click="setActiveTab('options')"
                :current="$activeTab === 'options'">
                Options ({{ $product->options->count() }})
            </flux:tab>
            <flux:tab 
                name="images" 
                icon="photo" 
                wire:click="setActiveTab('images')"
                :current="$activeTab === 'images'">
                Images ({{ $product->images->count() }})
            </flux:tab>
        </flux:tabs>

        <!-- Product Details Tab -->
        <flux:tab.panel name="details" :show="$activeTab === 'details'">
            <flux:card>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div>
                            <flux:field>
                                <flux:label>Product Title *</flux:label>
                                <flux:input wire:model="productForm.title" placeholder="Enter product title" />
                                <flux:error name="productForm.title" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Handle</flux:label>
                                <flux:input wire:model="productForm.handle" placeholder="product-handle" />
                                <flux:description>URL handle for the product</flux:description>
                                <flux:error name="productForm.handle" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Vendor</flux:label>
                                <flux:input wire:model="productForm.vendor" placeholder="Brand or vendor name" />
                                <flux:error name="productForm.vendor" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Product Type</flux:label>
                                <flux:input wire:model="productForm.product_type" placeholder="e.g., Clothing, Electronics" />
                                <flux:error name="productForm.product_type" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Tags</flux:label>
                                <flux:input wire:model="productForm.tags" placeholder="tag1, tag2, tag3" />
                                <flux:description>Comma-separated tags</flux:description>
                                <flux:error name="productForm.tags" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Published Date</flux:label>
                                <flux:input type="datetime-local" wire:model="productForm.published_at" />
                                <flux:description>Leave empty to keep as draft</flux:description>
                                <flux:error name="productForm.published_at" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div>
                            <flux:field>
                                <flux:label>Description</flux:label>
                                <flux:textarea 
                                    wire:model="productForm.body_html" 
                                    rows="10" 
                                    placeholder="Product description..." />
                                <flux:error name="productForm.body_html" />
                            </flux:field>
                        </div>

                        <!-- Product Preview -->
                        @if($product->primary_image_url)
                            <div>
                                <flux:label>Current Primary Image</flux:label>
                                <div class="mt-2 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                    <img src="{{ $product->primary_image_url }}" 
                                         alt="{{ $product->title }}"
                                         class="w-full h-48 object-cover">
                                </div>
                            </div>
                        @endif

                        <!-- Product Stats -->
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4">
                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-3">Product Statistics</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Variants:</span>
                                    <span class="font-medium ml-2">{{ $product->variants->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Options:</span>
                                    <span class="font-medium ml-2">{{ $product->options->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Images:</span>
                                    <span class="font-medium ml-2">{{ $product->images->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">Created:</span>
                                    <span class="font-medium ml-2">{{ $product->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </flux:card>
        </flux:tab.panel>

        <!-- Variants Tab -->
        <flux:tab.panel name="variants" :show="$activeTab === 'variants'">
            <flux:card>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Product Variants</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Manage different variations of this product</p>
                    </div>
                    <flux:button 
                        wire:click="$set('showAddVariant', true)" 
                        variant="primary" 
                        icon="plus" 
                        size="sm">
                        Add Variant
                    </flux:button>
                </div>

                <!-- Add New Variant Form -->
                @if($showAddVariant)
                    <div class="mb-6 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <h4 class="font-medium mb-4">Add New Variant</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>Title *</flux:label>
                                <flux:input wire:model="variantForms.new.title" placeholder="Variant title" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Price</flux:label>
                                <flux:input type="number" step="0.01" wire:model="variantForms.new.price" placeholder="0.00" />
                            </flux:field>
                            <flux:field>
                                <flux:label>SKU</flux:label>
                                <flux:input wire:model="variantForms.new.sku" placeholder="SKU-123" />
                            </flux:field>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <flux:button wire:click="addVariant" variant="primary" size="sm">Add Variant</flux:button>
                            <flux:button wire:click="$set('showAddVariant', false)" variant="ghost" size="sm">Cancel</flux:button>
                        </div>
                    </div>
                @endif

                <!-- Existing Variants -->
                <div class="space-y-4">
                    @forelse($product->variants as $variant)
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $variant->title }}</h4>
                                <flux:button 
                                    wire:click="deleteVariant({{ $variant->id }})"
                                    wire:confirm="Are you sure you want to delete this variant?"
                                    variant="ghost" 
                                    icon="trash" 
                                    size="xs"
                                    class="text-red-600 hover:text-red-700">
                                </flux:button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <flux:field>
                                    <flux:label>Title</flux:label>
                                    <flux:input 
                                        wire:model="variantForms.{{ $variant->id }}.title"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Price</flux:label>
                                    <flux:input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="variantForms.{{ $variant->id }}.price"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Compare Price</flux:label>
                                    <flux:input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="variantForms.{{ $variant->id }}.compare_at_price"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>SKU</flux:label>
                                    <flux:input 
                                        wire:model="variantForms.{{ $variant->id }}.sku"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <flux:field>
                                    <flux:label>Option 1</flux:label>
                                    <flux:input 
                                        wire:model="variantForms.{{ $variant->id }}.option1"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Option 2</flux:label>
                                    <flux:input 
                                        wire:model="variantForms.{{ $variant->id }}.option2"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Option 3</flux:label>
                                    <flux:input 
                                        wire:model="variantForms.{{ $variant->id }}.option3"
                                        wire:blur="updateVariant({{ $variant->id }})" />
                                </flux:field>
                            </div>

                            <div class="flex gap-4 mt-4">
                                <flux:field>
                                    <flux:checkbox 
                                        wire:model="variantForms.{{ $variant->id }}.available"
                                        wire:change="updateVariant({{ $variant->id }})">
                                    </flux:checkbox>
                                    <flux:label>
                                        Available
                                    </flux:label>
                                </flux:field>
                                <flux:field>
                                    <flux:checkbox 
                                        wire:model="variantForms.{{ $variant->id }}.requires_shipping"
                                        wire:change="updateVariant({{ $variant->id }})">
                                    </flux:checkbox>
                                    <flux:label>
                                        Requires Shipping
                                    </flux:label>
                                </flux:field>
                                <flux:field>
                                    <flux:checkbox 
                                        wire:model="variantForms.{{ $variant->id }}.taxable"
                                        wire:change="updateVariant({{ $variant->id }})">
                                    </flux:checkbox>
                                    <flux:label>
                                        Taxable
                                    </flux:label>
                                </flux:field>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="squares-2x2" class="size-12 mx-auto mb-3 opacity-50" />
                            <p>No variants found. Add your first variant to get started.</p>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </flux:tab.panel>

        <!-- Options Tab -->
        <flux:tab.panel name="options" :show="$activeTab === 'options'">
            <flux:card>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Product Options</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Define the available options for this product</p>
                    </div>
                    <flux:button 
                        wire:click="$set('showAddOption', true)" 
                        variant="primary" 
                        icon="plus" 
                        size="sm">
                        Add Option
                    </flux:button>
                </div>

                <!-- Existing Options -->
                <div class="space-y-4">
                    @forelse($product->options as $option)
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $option->name }}</h4>
                                <flux:button 
                                    wire:click="deleteOption({{ $option->id }})"
                                    wire:confirm="Are you sure you want to delete this option?"
                                    variant="ghost" 
                                    icon="trash" 
                                    size="xs"
                                    class="text-red-600 hover:text-red-700">
                                </flux:button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>Option Name</flux:label>
                                    <flux:input 
                                        wire:model="optionForms.{{ $option->id }}.name"
                                        wire:blur="updateOption({{ $option->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Values</flux:label>
                                    <flux:input 
                                        wire:model="optionForms.{{ $option->id }}.values"
                                        wire:blur="updateOption({{ $option->id }})"
                                        placeholder="value1, value2, value3" />
                                    <flux:description>Comma-separated values</flux:description>
                                </flux:field>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="adjustments-horizontal" class="size-12 mx-auto mb-3 opacity-50" />
                            <p>No options found. Add your first option to get started.</p>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </flux:tab.panel>

        <!-- Images Tab -->
        <flux:tab.panel name="images" :show="$activeTab === 'images'">
            <flux:card>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Product Images</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Manage product images and their display order</p>
                    </div>
                    <flux:button 
                        wire:click="$set('showAddImage', true)" 
                        variant="primary" 
                        icon="plus" 
                        size="sm">
                        Add Image
                    </flux:button>
                </div>

                <!-- Existing Images -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($product->images as $image)
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                            <div class="aspect-square bg-zinc-100 dark:bg-zinc-700">
                                <img src="{{ $image->src }}" 
                                     alt="Product image"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="p-4 space-y-3">
                                <flux:field>
                                    <flux:label>Image URL</flux:label>
                                    <flux:input 
                                        wire:model="imageForms.{{ $image->id }}.src"
                                        wire:blur="updateImage({{ $image->id }})" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Position</flux:label>
                                    <flux:input 
                                        type="number"
                                        wire:model="imageForms.{{ $image->id }}.position"
                                        wire:blur="updateImage({{ $image->id }})" />
                                </flux:field>
                                <flux:button 
                                    wire:click="deleteImage({{ $image->id }})"
                                    wire:confirm="Are you sure you want to delete this image?"
                                    variant="ghost" 
                                    icon="trash" 
                                    size="xs"
                                    class="w-full text-red-600 hover:text-red-700">
                                    Delete Image
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="photo" class="size-12 mx-auto mb-3 opacity-50" />
                            <p>No images found. Add your first image to get started.</p>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </flux:tab.panel>
    </flux:tab.group>
</div> 