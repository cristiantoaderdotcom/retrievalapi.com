<div>
    <flux:card class="relative @container">
		<div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-orange-100 opacity-60 blur-3xl"></div>
		<div class="absolute bottom-4 right-48 h-32 w-32 rounded-full bg-amber-100 opacity-70 blur-3xl"></div>

		<div class="flex flex-col @5xl:flex-row gap-6">
			<div class="flex-1">
				<div class="space-y-6">
					<div class="inline-flex items-center gap-2 text-green-600 dark:text-green-400">
						<flux:icon class="size-5" name="chat-bubble-left-right" />
						<span class="font-semibold">Manage AI Knowledge Base</span>
					</div>
					<flux:text class="text-sm">
						This page contains all the data your AI will use to generate responses.
						Every question and answer pair you create will train your AI to respond more accurately
						to similar queries. You can add, edit, or remove data examples at any time to refine your AI's knowledge.
					</flux:text>

					<div class="mt-4 flex flex-wrap gap-3">
						<flux:button class="border-0 bg-green-500 text-white shadow-sm hover:bg-green-600"
							icon="plus"
							variant="primary"
							wire:click="store"
							x-on:click="$nextTick(() => window.scrollTo({ top: $refs.accordion.offsetTop, behavior: 'smooth' }))">
							Add New Question and Answer
						</flux:button>

						<flux:button class="border-green-200 text-gree n-700 shadow-sm hover:border-green-300"
							href="{{ route('app.workspace.knowledge-base.import-content', $workspace->uuid) }}"
							icon="circle-stack"
							variant="outline"
							wire:navigate>
							Import Content
						</flux:button>
					</div>
				</div>
			</div>

		</div>

        <div class="mt-6" wire:poll.10s="checkAllProcessed">
            <flux:card class="flex-1 bg-white dark:bg-zinc-800 shadow-sm">
                <div class="flex flex-col md:flex-row gap-4 md:gap-6">
                    <!-- Stats Section -->
                    <div class="flex flex-1 gap-6 md:border-r border-zinc-200 dark:border-zinc-700 pr-0 md:pr-6">
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-green-100 dark:bg-green-800/30 p-2">
                                <flux:icon class="size-5 text-green-600 dark:text-green-400" name="document-text" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total Q&As</div>
                                <div class="text-lg font-bold">{{ $totalCount }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-blue-100 dark:bg-blue-800/30 p-2">
                                <flux:icon class="size-5 text-blue-600 dark:text-blue-400" name="check-circle" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Trained</div>
                                <div class="text-lg font-bold">{{ $trainedCount }}</div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 items-center">
                            <div class="rounded-md bg-yellow-100 dark:bg-yellow-800/30 p-2">
                                <flux:icon class="size-5 text-yellow-600 dark:text-yellow-400" name="clock" />
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">Processing</div>
                                <div class="text-lg font-bold">{{ $processingCount }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Section -->
                    <div class="flex-1">
                        @if ($allProcessed)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-green-100 dark:bg-green-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-green-600 dark:text-green-400" name="check-circle" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center flex-wrap gap-1">
                                        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Knowledge Base Ready</h3>
                                        @if ($totalCount < 100)
                                            <span class="ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs">Low Content</span>
                                        @elseif ($totalCount < 250)
                                            <span class="ml-1 px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs">Medium Content</span>
                                        @else
                                            <span class="ml-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs">High Content</span>
                                        @endif
                                    </div>
                                    
                                    @if ($totalCount < 100)
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                            <span class="font-medium">Limited AI training detected.</span> With only {{ $totalCount }} Q&As, your AI may struggle with diverse queries. Consider adding at least 100 more entries for better response quality.
                                        </p>
                                    @elseif ($totalCount < 250)
                                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                            <span class="font-medium">Basic AI training complete.</span> With {{ $totalCount }} Q&As, your AI can handle common queries. Adding more examples (aim for 250+) will improve response accuracy and coverage.
                                        </p>
                                    @else
                                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                            <span class="font-medium">Robust AI training achieved.</span> With {{ $totalCount }} Q&As, your AI has excellent training data. Your knowledge base is well-positioned to provide accurate responses.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex items-start">
                                <div class="flex-shrink-0 rounded-md bg-yellow-100 dark:bg-yellow-800/30 p-2 mr-3">
                                    <flux:icon class="size-5 text-yellow-600 dark:text-yellow-400" name="clock" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Training in Progress</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        {{ $processingCount }} of {{ $totalCount }} Q&As are still being processed. This typically takes a few minutes before your AI can use them for responding to queries.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>

		{{-- <div class="mt-6 rounded-xl bg-zinc-200 dark:bg-zinc-700 p-4">
			<div class="flex items-start gap-3">
				<div class="rounded-lg bg-green-100 p-1">
					<flux:icon class="size-5 text-green-600" name="light-bulb" />
				</div>
				<div>
					<h3 class="mb-1 text-sm font-medium text-zinc-700 dark:text-zinc-300">Tips for effective training</h3>
					<ul class="list-disc space-y-1 pl-5 text-sm text-zinc-700 dark:text-zinc-300">
						<li>Add diverse questions using different phrasings to improve response accuracy</li>
						<li>Keep answers concise and focused on the specific question</li>
						<li>Include industry-specific terminology your customers might use</li>
						<li>Regularly review and update your data as your products or services change</li>
					</ul>
				</div>
			</div>
		</div> --}}
	</flux:card>	

	<div class="mt-6">
		<div class="flex items-center gap-2">
			<flux:icon class="size-5 text-green-600" name="chat-bubble-bottom-center-text" />
			<flux:heading class="">Questions and Answers</flux:heading>
		</div>
		<div class="flex items-center gap-2">
			<p class="mt-1 text-xs">Manage all question and answer pairs your chatbot will use for training.</p>

		</div>
	</div>

	<flux:card class="mt-2">
		<div class="rounded-t-xl border-b border-zinc-200 dark:border-zinc-700 -mx-6 -mt-6 mb-6 bg-gray-50 dark:bg-gray-800 p-3">
			<div class="flex items-center gap-2">
				<form wire:submit="resetPage" class="flex items-center gap-3">
					<flux:input icon="magnifying-glass" wire:model="search" placeholder="Search..." size="sm" class="max-w-fit"/>

					<flux:button type="submit" size="sm">Filter</flux:button>

					@if ($this->search ?? false)
						<flux:button wire:click="set('search', ''); $wire.resetPage()" size="sm" variant="ghost">Clear all</flux:button>
					@endif
				</form>
			</div>
		</div>

		<flux:table :paginate="$knowledgeBases">
			<flux:table.columns>
				<flux:table.column>Question</flux:table.column>
				<flux:table.column>Answer</flux:table.column>
			</flux:table.columns>
			<flux:table.rows>
				@forelse($knowledgeBases as $knowledgeBase)
					<flux:table.row wire:key="row-{{ $knowledgeBase->id }}" @class(['border-transparent', 'bg-gray-100 dark:bg-zinc-700' => $loop->iteration % 2 === 0])>
						<flux:table.cell class="w-1/2">
							<div class="flex items-start">
								<flux:textarea 
									class="min-w-96"
									rows="3"
									wire:blur="update({{ $knowledgeBase->id }})"
									wire:model.blur="form.{{ $knowledgeBase->id }}.question" />
							</div>
						</flux:table.cell>
						<flux:table.cell class="w-1/2">
							<flux:textarea 
								class="min-w-96"
								rows="3"
								wire:blur="update({{ $knowledgeBase->id }})"
								wire:model.blur="form.{{ $knowledgeBase->id }}.answer" />
						</flux:table.cell>
					</flux:table.row>
					<flux:table.row wire:key="row-{{ $knowledgeBase->id }}-sub" @class(['bg-gray-100 dark:bg-zinc-700' => $loop->iteration % 2 === 0])>
						<flux:table.cell colspan="3" class="pt-0">
							<div class="flex items-center gap-2">

								@if ($knowledgeBase->created_at->gt(now()->subMinutes(10)))
									<flux:badge color="blue"
										icon="clock"
										size="xs">New</flux:badge>
								@endif

								@if ($knowledgeBase->embedding_processed_at)
									<flux:badge color="green"
										icon="check"
										size="xs">Trained</flux:badge>
								@else
									<flux:badge color="yellow"
										icon="clock"
										size="xs">Processing</flux:badge>
								@endif

								@if ($knowledgeBase->similarity_score)
									@if ($knowledgeBase->similarity_score < 0.3)
										<flux:badge color="gray"
											size="xs">Similarity Rank: Unique ({{ number_format($knowledgeBase->similarity_score, 2) }})</flux:badge>
									@elseif ($knowledgeBase->similarity_score < 0.4)
										<flux:badge color="gray"
											size="xs">Similarity Rank: Distinct ({{ number_format($knowledgeBase->similarity_score, 2) }})</flux:badge>
									@elseif ($knowledgeBase->similarity_score < 0.5)
										<flux:badge color="gray"
											size="xs">Similarity Rank: Common ({{ number_format($knowledgeBase->similarity_score, 2) }})</flux:badge>
									@else
										<flux:badge color="gray"
											icon="exclamation-triangle"
											size="xs">Similarity Rank: Too Similar ({{ number_format($knowledgeBase->similarity_score, 2) }})</flux:badge>
									@endif
								@endif
                                   
								@if ($knowledgeBase->knowledgeBaseResource)
									@if ($knowledgeBase->knowledgeBaseResource->resourceable instanceof \App\Models\KnowledgeBaseTextResource)
										<div class="text-xs text-zinc-500">
											<flux:badge size="xs" class="mr-1">Website</flux:badge>
											<a class="underline hover:text-orange-600"
												href="{{ $knowledgeBase->knowledgeBaseResource->resourceable->url }}"
												target="_blank">
												{{ $knowledgeBase->knowledgeBaseResource->resourceable->url }}
											</a>
										</div>
									@elseif($knowledgeBase->knowledgeBaseResource->resourceable instanceof \App\Models\KnowledgeBaseFileResource)
										<div class="text-xs text-zinc-500">
											<flux:badge size="xs" class="mr-1">File</flux:badge>
											{{ $knowledgeBase->knowledgeBaseResource->resourceable->name }}
										</div>
									@elseif($knowledgeBase->knowledgeBaseResource->resourceable instanceof \App\Models\KnowledgeBaseTextResource)
										<div class="text-xs text-zinc-500">
											<flux:badge size="xs">Text</flux:badge>
										</div>
									@endif
								@endif

								<flux:spacer />


								<flux:badge as="button"
									icon="trash"
									size="xs"
									wire:click="delete({{ $knowledgeBase->id }})"
									wire:confirm="Are you sure you want to delete this data example?"
								>
									Delete
								</flux:badge>
							</div>
						</flux:table.cell>
					</flux:table.row>
				@empty
					<flux:table.row>
						<flux:table.cell colspan="3">
							<div class="flex items-center justify-center py-8 text-zinc-400">
								No questions and answers found
							</div>
						</flux:table.cell>
					</flux:table.row>
				@endforelse
			</flux:table.rows>
		</flux:table>
	</flux:card>
</div>
