<div>
    <flux:card class="relative @container">
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-blue-100 opacity-60 blur-3xl"></div>
        <div class="absolute bottom-4 right-48 h-32 w-32 rounded-full bg-indigo-100 opacity-70 blur-3xl"></div>

        <div class="flex flex-col @5xl:flex-row gap-6">
            <div class="flex-1">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 text-green-600 dark:text-green-400">
                        <flux:icon class="size-5" name="cube" />
                        <span class="font-semibold">Product Catalog</span>
                    </div>
                    <flux:text class="text-sm">
                        Manage your product inventory and catalog. Products are used by your AI to provide accurate information about your offerings, pricing, and availability. Keep your catalog updated to ensure customers receive the most current product details.
                    </flux:text>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <flux:button class="border-0 bg-green-500 text-white shadow-sm hover:bg-green-600"
                            icon="plus"
                            variant="primary">
                            Add Product Manually
                        </flux:button>

                        <flux:button class="border-green-200 text-green-700 shadow-sm hover:border-green-300"
                            href="{{ route('app.workspace.knowledge-base.product-feeds', $workspace->uuid) }}"
                            icon="rss"
                            variant="outline"
                            wire:navigate>
                            Connect Product Feed
                        </flux:button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="mt-6">
            <flux:card class="flex-1 bg-white dark:bg-zinc-800 shadow-sm">
                <div class="flex flex-col md:flex-row gap-4 md:gap-6">
                    <div class="flex flex-1 gap-6 md:border-r border-zinc-200 dark:border-zinc-700 pr-0 md:pr-6">
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-green-100 dark:bg-green-800/30 p-2">
                                <flux:icon class="size-5 text-green-600 dark:text-green-400" name="cube" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total Products</div>
                                <div class="text-lg font-bold">{{ number_format($totalProducts) }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-green-100 dark:bg-green-800/30 p-2">
                                <flux:icon class="size-5 text-green-600 dark:text-green-400" name="eye" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Published</div>
                                <div class="text-lg font-bold">{{ number_format($publishedProducts) }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-purple-100 dark:bg-purple-800/30 p-2">
                                <flux:icon class="size-5 text-purple-600 dark:text-purple-400" name="squares-2x2" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Variants</div>
                                <div class="text-lg font-bold">{{ number_format($totalVariants) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Catalog Health Section -->
                    <div class="flex-1">
                        @if ($totalProducts > 0)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-blue-100 dark:bg-blue-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-green-600 dark:text-green-400" name="chart-bar" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center flex-wrap gap-1">
                                        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Catalog Health</h3>
                                        @php
                                            $publishedPercentage = $totalProducts > 0 ? ($publishedProducts / $totalProducts) * 100 : 0;
                                        @endphp
                                        @if ($publishedPercentage >= 80)
                                            <span class="ml-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs">Excellent</span>
                                        @elseif ($publishedPercentage >= 60)
                                            <span class="ml-1 px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs">Good</span>
                                        @else
                                            <span class="ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs">Needs Attention</span>
                                        @endif
                                    </div>
                                    
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        <span class="font-medium">{{ number_format($publishedPercentage, 1) }}% of products are published.</span>
                                        @if ($publishedPercentage < 80)
                                            Consider publishing more products to improve customer experience.
                                        @else
                                            Your catalog is well-maintained and customer-ready.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-gray-100 dark:bg-gray-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-gray-600 dark:text-gray-400" name="cube" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Empty Catalog</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        Start building your product catalog by adding products manually or connecting a product feed.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </flux:card>

    <!-- Products Section -->
    <div class="mt-6">
        <div class="flex items-center gap-2">
            <flux:icon class="size-5 text-green-600" name="cube-transparent" />
            <flux:heading class="">Products</flux:heading>
        </div>
        <div class="flex items-center gap-2">
            <p class="mt-1 text-xs">Browse and manage your product inventory.</p>
        </div>
    </div>

    <flux:card class="mt-2">
        <!-- Search and Filters -->
        <div class="rounded-t-xl border-b border-zinc-200 dark:border-zinc-700 -mx-6 -mt-6 mb-6 bg-gray-50 dark:bg-gray-800 p-3">
            <div class="flex items-center gap-2">
                <form wire:submit="$refresh" class="flex items-center gap-3">
                    <flux:input icon="magnifying-glass" wire:model="search" placeholder="Search products, vendors, types..." size="sm" class="max-w-fit"/>

                    <flux:button type="submit" size="sm">Filter</flux:button>

                    @if ($this->search ?? false)
                        <flux:button wire:click="set('search', ''); $refresh" size="sm" variant="ghost">Clear all</flux:button>
                    @endif
                </form>
            </div>
        </div>

        @if($products->count() > 0)
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                @foreach($products as $product)
                    <div class="group relative bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-green-300 dark:hover:border-green-600 transition-all duration-200 hover:shadow-md">
                        <!-- Product Image -->
                        <div class="aspect-square bg-zinc-100 dark:bg-zinc-700 rounded-t-lg overflow-hidden">
                            @if($product->primary_image_url)
                                <img src="{{ $product->primary_image_url }}" 
                                     alt="{{ $product->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <flux:icon class="size-12 text-zinc-400" name="photo" />
                                </div>
                            @endif
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 left-2">
                                @if($product->published_at)
                                    <flux:badge color="green" variant="solid" class="text-white" size="xs">Published</flux:badge>
                                @else
                                    <flux:badge color="gray" variant="solid" size="xs">Draft</flux:badge>
                                @endif
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <div class="space-y-2">
                                <h3 class="font-medium text-sm text-zinc-900 dark:text-zinc-100 line-clamp-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                    {{ $product->title }}
                                </h3>
                                
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($product->vendor)
                                        <span class="truncate">{{ $product->vendor }}</span>
                                    @endif
                                    @if($product->variants->count() > 1)
                                        <span class="ml-auto">{{ $product->variants->count() }} variants</span>
                                    @endif
                                </div>

                                @if($product->product_type)
                                    <div class="text-xs">
                                        <flux:badge size="xs" variant="outline">{{ $product->product_type }}</flux:badge>
                                    </div>
                                @endif

                                <!-- Price Range -->
                                @if($product->variants->count() > 0)
                                    @php
                                        $prices = $product->variants->pluck('price')->filter();
                                        $minPrice = $prices->min();
                                        $maxPrice = $prices->max();
                                    @endphp
                                    @if($prices->count() > 0)
                                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                            @if($minPrice == $maxPrice)
                                                ${{ number_format($minPrice, 2) }}
                                            @else
                                                ${{ number_format($minPrice, 2) }} - ${{ number_format($maxPrice, 2) }}
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 flex gap-2">
                                <flux:button size="xs" variant="outline" icon="pencil" class="flex-1" wire:click="manageProduct({{ $product->id }})">
                                    Manage Product
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                    <flux:icon class="size-12 text-zinc-400" name="cube" />
                </div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                    @if($search)
                        No products found
                    @else
                        No products yet
                    @endif
                </h3>
                <p class="text-zinc-500 dark:text-zinc-400 mb-6 max-w-md mx-auto">
                    @if($search)
                        Try adjusting your search terms or clear the filters to see all products.
                    @else
                        Start building your product catalog by adding products manually or connecting a product feed to get started.
                    @endif
                </p>
                @if(!$search)
                    <div class="flex justify-center gap-3">
                        <flux:button icon="plus" variant="primary">
                            Add Product Manually
                        </flux:button>
                        <flux:button 
                            href="{{ route('app.workspace.knowledge-base.product-feeds', $workspace->uuid) }}"
                            icon="rss" 
                            variant="outline"
                            wire:navigate>
                            Connect Product Feed
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    </flux:card>
</div>
