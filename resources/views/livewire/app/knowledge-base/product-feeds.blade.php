<div>
    <flux:card class="relative @container">
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-purple-100 opacity-60 blur-3xl"></div>
        <div class="absolute bottom-4 right-48 h-32 w-32 rounded-full bg-indigo-100 opacity-70 blur-3xl"></div>

        <div class="flex flex-col @5xl:flex-row gap-6">
            <div class="flex-1">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 text-green-600 dark:text-green-400">
                        <flux:icon class="size-5" name="rss" />
                        <span class="font-semibold">Product Feeds</span>
                    </div>
                    <flux:text class="text-sm">
                        Connect and manage product feeds from external sources like Shopify, WooCommerce, or custom APIs. Product feeds automatically sync your inventory data, keeping your AI assistant up-to-date with the latest product information, pricing, and availability.
                    </flux:text>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <flux:button class="border-0 bg-green-500 text-white shadow-sm hover:bg-green-600"
                            icon="plus"
                            variant="primary"
                            wire:click="createFeed">
                            Add Product Feed
                        </flux:button>

                        <flux:button class="border-green-200 text-green-700 shadow-sm hover:border-green-300"
                            href="{{ route('app.workspace.knowledge-base.product-catalog', $workspace->uuid) }}"
                            icon="cube"
                            variant="outline"
                            wire:navigate>
                            View Product Catalog
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
                            <div class="rounded-md bg-purple-100 dark:bg-purple-800/30 p-2">
                                <flux:icon class="size-5 text-purple-600 dark:text-purple-400" name="rss" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total Feeds</div>
                                <div class="text-lg font-bold">{{ number_format($totalFeeds) }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-green-100 dark:bg-green-800/30 p-2">
                                <flux:icon class="size-5 text-green-600 dark:text-green-400" name="check-circle" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Active</div>
                                <div class="text-lg font-bold">{{ number_format($activeFeeds) }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-red-100 dark:bg-red-800/30 p-2">
                                <flux:icon class="size-5 text-red-600 dark:text-red-400" name="exclamation-triangle" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Errors</div>
                                <div class="text-lg font-bold">{{ number_format($errorFeeds) }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-blue-100 dark:bg-blue-800/30 p-2">
                                <flux:icon class="size-5 text-blue-600 dark:text-blue-400" name="cube" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Products</div>
                                <div class="text-lg font-bold">{{ number_format($totalProducts) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feed Health Section -->
                    <div class="flex-1">
                        @if ($totalFeeds > 0)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-blue-100 dark:bg-blue-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-blue-600 dark:text-blue-400" name="chart-bar" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center flex-wrap gap-1">
                                        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Feed Health</h3>
                                        @php
                                            $healthPercentage = $totalFeeds > 0 ? (($totalFeeds - $errorFeeds) / $totalFeeds) * 100 : 0;
                                        @endphp
                                        @if ($healthPercentage >= 90)
                                            <span class="ml-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs">Excellent</span>
                                        @elseif ($healthPercentage >= 70)
                                            <span class="ml-1 px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs">Good</span>
                                        @else
                                            <span class="ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs">Needs Attention</span>
                                        @endif
                                    </div>
                                    
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        <span class="font-medium">{{ number_format($healthPercentage, 1) }}% of feeds are healthy.</span>
                                        @if ($errorFeeds > 0)
                                            {{ $errorFeeds }} feed{{ $errorFeeds > 1 ? 's' : '' }} need{{ $errorFeeds === 1 ? 's' : '' }} attention.
                                        @else
                                            All feeds are operating normally.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-gray-100 dark:bg-gray-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-gray-600 dark:text-gray-400" name="rss" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No Feeds Connected</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        Connect your first product feed to start syncing inventory data with your AI assistant.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </flux:card>

    <!-- Feeds Section -->
    <div class="mt-6">
        <div class="flex items-center gap-2">
            <flux:icon class="size-5 text-green-600" name="server" />
            <flux:heading class="">Connected Feeds</flux:heading>
        </div>
        <div class="flex items-center gap-2">
            <p class="mt-1 text-xs">Manage your product feed connections and sync settings.</p>
        </div>
    </div>

    <flux:card class="mt-2">
        <!-- Search and Filters -->
        <div class="rounded-t-xl border-b border-zinc-200 dark:border-zinc-700 -mx-6 -mt-6 mb-6 bg-gray-50 dark:bg-gray-800 p-3">
            <div class="flex items-center gap-2">
                <form wire:submit="$refresh" class="flex items-center gap-3">
                    <flux:input icon="magnifying-glass" wire:model="search" placeholder="Search feeds..." size="sm" class="max-w-fit"/>

                    <flux:button type="submit" size="sm">Filter</flux:button>

                    @if ($this->search ?? false)
                        <flux:button wire:click="set('search', ''); $refresh" size="sm" variant="ghost">Clear all</flux:button>
                    @endif
                </form>
            </div>
        </div>

        @if($feeds->count() > 0)
            <!-- Feeds Table -->
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Feed Details</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Products</flux:table.column>
                    <flux:table.column>Last Sync</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($feeds as $feed)
                        <flux:table.row wire:key="feed-{{ $feed->id }}">
                            <flux:table.cell>
                                <div class="space-y-1">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $feed->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate max-w-md">{{ $feed->url }}</div>
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="xs" variant="outline">{{ ucfirst($feed->provider) }}</flux:badge>
                                        <span class="text-xs text-zinc-400">•</span>
                                        <span class="text-xs text-zinc-500">
                                            Sync every 
                                            @if($feed->scan_frequency >= 1440)
                                                {{ number_format($feed->scan_frequency / 1440, 1) }} day{{ $feed->scan_frequency >= 2880 ? 's' : '' }}
                                            @elseif($feed->scan_frequency >= 60)
                                                {{ number_format($feed->scan_frequency / 60, 1) }} hour{{ $feed->scan_frequency >= 120 ? 's' : '' }}
                                            @else
                                                {{ $feed->scan_frequency }} minute{{ $feed->scan_frequency > 1 ? 's' : '' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="space-y-1">
                                    <flux:badge 
                                        size="sm" 
                                        :color="$feed->status->color()"
                                        :icon="$feed->status === \App\Enums\ProductFeedStatus::PROCESSING ? 'clock' : ($feed->status === \App\Enums\ProductFeedStatus::ERROR ? 'exclamation-triangle' : 'check-circle')">
                                        {{ $feed->status->label() }}
                                    </flux:badge>
                                    @if($feed->error_message)
                                        <div class="text-xs text-red-600 dark:text-red-400 max-w-xs truncate" title="{{ $feed->error_message }}">
                                            {{ $feed->error_message }}
                                        </div>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($feed->products_count) }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">products</div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($feed->last_processed_at)
                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $feed->last_processed_at->diffForHumans() }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $feed->last_processed_at->format('M j, Y g:i A') }}</div>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Never</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button 
                                        size="xs" 
                                        variant="outline" 
                                        icon="arrow-path"
                                        wire:click="processFeed({{ $feed->id }})"
                                        :disabled="$feed->status === \App\Enums\ProductFeedStatus::PROCESSING">
                                        Sync
                                    </flux:button>
                                    
                                    <flux:button 
                                        size="xs" 
                                        variant="outline" 
                                        icon="wifi"
                                        wire:click="testFeed({{ $feed->id }})">
                                        Test
                                    </flux:button>
                                    
                                    <flux:button 
                                        size="xs" 
                                        variant="outline" 
                                        icon="pencil"
                                        wire:click="editFeed({{ $feed->id }})">
                                        Edit
                                    </flux:button>
                                    
                                    <flux:button 
                                        size="xs" 
                                        variant="outline" 
                                        icon="trash"
                                        color="red"
                                        wire:click="confirmDelete({{ $feed->id }})">
                                        Delete
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $feeds->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                    <flux:icon class="size-12 text-zinc-400" name="rss" />
                </div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                    @if($search)
                        No feeds found
                    @else
                        No product feeds connected
                    @endif
                </h3>
                <p class="text-zinc-500 dark:text-zinc-400 mb-6 max-w-md mx-auto">
                    @if($search)
                        Try adjusting your search terms or clear the filters to see all feeds.
                    @else
                        Connect your first product feed to start syncing inventory data. Supported platforms include Shopify, WooCommerce, and custom APIs.
                    @endif
                </p>
                @if(!$search)
                    <flux:button icon="plus" variant="primary" wire:click="createFeed">
                        Add Product Feed
                    </flux:button>
                @endif
            </div>
        @endif
    </flux:card>

    <!-- Create Feed Modal -->
    <flux:modal name="create-feed" wire:model="showCreateModal">
        <form wire:submit="storeFeed">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add Product Feed</flux:heading>
                    <flux:subheading>Connect a new product feed to sync inventory data.</flux:subheading>
                </div>

                <div class="space-y-4">
                    <flux:input 
                        label="Feed Name" 
                        wire:model="feedForm.name" 
                        placeholder="e.g., Main Store Products"
                        required />

                    <flux:input 
                        label="Feed URL" 
                        wire:model="feedForm.url" 
                        placeholder="https://your-store.myshopify.com/products.json"
                        type="url"
                        required />

                    <flux:select label="Provider" wire:model="feedForm.provider" required>
                        <flux:select.option value="shopify">Shopify</flux:select.option>
                    </flux:select>

                    <flux:select label="Sync Frequency" wire:model="feedForm.scan_frequency" required>
                        <flux:select.option value="60">Every Hour</flux:select.option>
                        <flux:select.option value="360">Every 6 Hours</flux:select.option>
                        <flux:select.option value="720">Every 12 Hours</flux:select.option>
                        <flux:select.option value="1440">Daily</flux:select.option>
                        <flux:select.option value="10080">Weekly</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button variant="ghost" wire:click="$set('showCreateModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Add Feed</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Edit Feed Modal -->
    <flux:modal name="edit-feed" wire:model="showEditModal">
        <form wire:submit="updateFeed">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Edit Product Feed</flux:heading>
                    <flux:subheading>Update feed settings and configuration.</flux:subheading>
                </div>

                <div class="space-y-4">
                    <flux:input 
                        label="Feed Name" 
                        wire:model="feedForm.name" 
                        placeholder="e.g., Main Store Products"
                        required />

                    <flux:input 
                        label="Feed URL" 
                        wire:model="feedForm.url" 
                        placeholder="https://your-store.myshopify.com/products.json"
                        type="url"
                        required />

                    <flux:select label="Provider" wire:model="feedForm.provider" required>
                        <flux:select.option value="shopify">Shopify</flux:select.option>
                    </flux:select>

                    <flux:select label="Sync Frequency" wire:model="feedForm.scan_frequency" required>
                        <flux:select.option value="60">Every Hour</flux:select.option>
                        <flux:select.option value="360">Every 6 Hours</flux:select.option>
                        <flux:select.option value="720">Every 12 Hours</flux:select.option>
                        <flux:select.option value="1440">Daily</flux:select.option>
                        <flux:select.option value="10080">Weekly</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button variant="ghost" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Update Feed</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-feed" wire:model="showDeleteModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Product Feed</flux:heading>
                <flux:subheading>This action cannot be undone. All products from this feed will be permanently deleted.</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deleteFeed">Delete Feed</flux:button>
            </div>
        </div>
    </flux:modal>
</div> 