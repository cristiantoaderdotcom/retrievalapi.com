<div>
    @push('scripts')
	<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
	<script>
		// Configure marked options
		marked.setOptions({
			breaks: true,
			gfm: true
		});

		// Add a new Alpine.js directive for markdown
		document.addEventListener('alpine:init', () => {
			Alpine.directive('markdown', (el, {}, { effect }) => {
				effect(() => {
					const content = el.getAttribute('data-content');
					if (content) {
						el.innerHTML = marked.parse(content);
						
						// Apply styling if in a text-white container
						const parentHasTextWhite = el.closest('.text-white') !== null;
						if (parentHasTextWhite) {
							el.querySelectorAll('a').forEach(a => a.classList.add('text-blue-300'));
							el.querySelectorAll('code, pre').forEach(codeEl => {
								codeEl.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
								codeEl.style.color = 'white';
							});
						}
					}
				});
			});
		});
		
		// Use a small delay to ensure the DOM is fully loaded and updated
		function init() {
			// Check if there's an initial conversation loaded
			const initialConversation = document.getElementById('initial-conversation');
			if (initialConversation && initialConversation.getAttribute('data-loaded') === 'true') {
				console.log('Initial conversation detected, parsing markdown');
				// Parse multiple times with increasing delays
				parseMarkdown();
				setTimeout(parseMarkdown, 50);
				setTimeout(parseMarkdown, 100);
				setTimeout(parseMarkdown, 300);
				setTimeout(parseMarkdown, 500);
				setTimeout(parseMarkdown, 1000);
			} else {
				// Just parse once for the initial page load without conversation
				parseMarkdown();
			}
			
			// Listen for Livewire events
			document.addEventListener('livewire:load', function() {
				// Listen for when a conversation is shown
				Livewire.on('conversationShown', function() {
					console.log('Conversation shown event received, parsing markdown');
					parseMarkdown();
					setTimeout(parseMarkdown, 50);
					setTimeout(parseMarkdown, 100);
					setTimeout(parseMarkdown, 300);
					setTimeout(parseMarkdown, 500);
				});
			});
			
			// Parse markdown after Livewire updates
			document.addEventListener('livewire:update', () => {
				console.log('Livewire update detected, parsing markdown');
				parseMarkdown();
				setTimeout(parseMarkdown, 50);
			});
		}
		
		// Different ways to ensure our code runs when the page is ready
		if (document.readyState === 'complete' || document.readyState === 'interactive') {
			// Document already ready
			setTimeout(init, 1);
		} else {
			// Wait for document to be ready
			document.addEventListener('DOMContentLoaded', init);
		}
		
		// Also run after window is fully loaded (including all resources)
		window.addEventListener('load', function() {
			console.log('Window load event, parsing markdown');
			parseMarkdown();
			setTimeout(parseMarkdown, 100);
		});

		function parseMarkdown() {
			console.log('Parsing markdown...');
			document.querySelectorAll('.markdown-content').forEach(function(element) {
				const content = element.getAttribute('data-content');
				if (content) {
					console.log('Found content to parse');
					element.innerHTML = marked.parse(content);
					
					// Apply text-white class to links, code, etc. if parent has text-white
					const parentHasTextWhite = element.closest('.text-white') !== null;
					if (parentHasTextWhite) {
						element.querySelectorAll('a').forEach(a => a.classList.add('text-blue-300'));
						element.querySelectorAll('code, pre').forEach(el => {
							el.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
							el.style.color = 'white';
						});
					}
				}
			});
		}
	</script>
	@endpush

	<style>
		/* Markdown styles for chat bubbles */
		.markdown-content {
			overflow-wrap: break-word;
			word-break: break-word;
		}
		.markdown-content p {
			margin-bottom: 0.5rem;
		}
		.markdown-content p:last-child {
			margin-bottom: 0;
		}
		.markdown-content ul, 
		.markdown-content ol {
			margin-left: 1.5rem;
			margin-bottom: 0.5rem;
		}
		.markdown-content h1, 
		.markdown-content h2, 
		.markdown-content h3, 
		.markdown-content h4 {
			margin-top: 0.5rem;
			margin-bottom: 0.5rem;
			font-weight: 600;
		}
		.markdown-content pre, 
		.markdown-content code {
			background-color: rgba(0, 0, 0, 0.05);
			border-radius: 0.25rem;
			padding: 0.1rem 0.25rem;
			font-family: monospace;
			font-size: 0.9em;
			overflow-x: auto;
		}
		.markdown-content pre {
			padding: 0.5rem;
			overflow-x: auto;
			margin-bottom: 0.5rem;
			max-width: 100%;
		}
		.markdown-content pre code {
			background-color: transparent;
			padding: 0;
		}
		.markdown-content blockquote {
			border-left: 3px solid rgba(0, 0, 0, 0.1);
			padding-left: 0.5rem;
			margin-left: 0.5rem;
			margin-bottom: 0.5rem;
		}
		.markdown-content img {
			max-width: 100%;
			height: auto;
		}
		.text-white .markdown-content pre,
		.text-white .markdown-content code {
			background-color: rgba(255, 255, 255, 0.15);
			color: white;
		}
		.text-white .markdown-content a {
			color: #a7c6ff;
		}
		.text-white .markdown-content blockquote {
			border-left-color: rgba(255, 255, 255, 0.3);
		}
		
		@media (max-width: 640px) {
			.markdown-content pre {
				max-width: calc(100vw - 6rem);
			}
		}
	</style>

	<flux:card class="!p-0">
		<div class="flex w-full flex-col lg:flex-row">
			<div class="w-full lg:max-w-sm lg:min-w-[300px]">
				<div class="border-b lg:border-b-0">
					<form wire:submit.prevent="resetPage" class="p-3 sm:p-4 relative">
						<flux:input wire:model.live="search" placeholder="Search conversations..." icon="magnifying-glass">
							@if($this->search)
								<x-slot name="iconTrailing">
									<flux:button wire:click="set('search', ''); $wire.resetPage()" size="sm" variant="subtle" icon="x-mark" class="-mr-1" />
								</x-slot>
							@endif
						</flux:input>
					</form>

					<ul class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
						@foreach($conversations as $conversation)
							<li wire:click="show('{{ $conversation->uuid }}')" 
								wire:key="conversation-{{ $conversation->id }}"
								@class([
									'relative cursor-pointer p-3 sm:p-6 py-3 sm:py-5 pr-2 sm:pr-4 hover:bg-gray-100 dark:hover:bg-gray-800',
									'bg-gray-100 dark:bg-zinc-700' => $this->conversation && $this->conversation->id === $conversation->id,
								])>
								<div class="flex flex-row gap-2">
									<div class="flex-1 overflow-hidden">
										<div class="flex items-center justify-between space-x-2 sm:space-x-3">
											<flux:badge size="xs" variant="pill">
												<span class="w-8 sm:w-12 truncate text-center">
													{{ $conversation->workspace->name }}
												</span>
											</flux:badge>

											<div class="w-1 flex-1 truncate font-semibold text-xs sm:text-sm ">
												{{ str(strip_tags($conversation->userMessage?->message))->limit(30) }}
											</div>

											@if($conversation->read_at && $conversation->read_at->gt($conversation->userMessage->created_at))
												<flux:badge icon="check" size="xs" variant="pill">Read</flux:badge>
											@else
												<flux:badge icon="eye" size="xs" variant="pill" color="indigo">Unread</flux:badge>
											@endif
										</div>
										<div class="flex items-center justify-between space-x-2 sm:space-x-3">
											<div class="line-clamp-1 text-xs  w-2/3 truncate">
												{{ str(strip_tags($conversation->assistantMessage?->message))->limit(30) }}
											</div>
											@if($conversation->userMessage)
												<time datetime="{{ $conversation->userMessage->created_at }}" class="shrink-0 whitespace-nowrap text-xs">
													<span>{{ $conversation->userMessage->created_at->diffForHumans() }}</span>
												</time>
											@endif
										</div>
										
										@php
											$dislikedCount = $conversation->messages->where('disliked', true)->count();
										@endphp
										@if($dislikedCount > 0)
											<div class="mt-1 flex items-center">
												<flux:badge size="xs" variant="pill" color="red" class="text-[10px]">
													<div class="flex items-center gap-1">
														<flux:icon name="hand-thumb-down" variant="micro"/>
														<span>{{ $dislikedCount }} disliked</span>
													</div>
												</flux:badge>
											</div>
										@endif
									</div>
								</div>
							</li>
						@endforeach
					</ul>

					<div class="p-3 sm:p-4">
						<flux:pagination :paginator="$conversations" />
					</div>
				</div>
			</div>
			<div class="relative flex-1 border-l border-zinc-200 dark:border-zinc-700">
				<div class="border-b lg:border-b-0">
					<div class="group relative flex flex-col justify-center p-3 sm:p-6 space-y-4 sm:space-y-6 min-h-[46rem]">
						@if($this->conversation)
							<div class="relative flex w-full flex-1 flex-col space-y-3 sm:space-y-5 px-2 sm:px-5" 
								x-data="{
									init() {
										// Directly call parseMarkdown when this container initializes
										setTimeout(parseMarkdown, 10);
										setTimeout(parseMarkdown, 100);
										setTimeout(parseMarkdown, 500);
									}
								}">
								<div class="flex flex-wrap gap-3 shadow-lg z-10 p-2 sm:p-4 rounded-b-lg -mx-2">
									
									<div class="flex flex-col justify-start gap-2 text-xs  flex-1 min-w-[200px]">
										@if($this->conversation->source)
											<div class="inline-flex items-start gap-2">
												<flux:icon name="building-storefront" variant="micro"/>
												<div>
													{{ $this->conversation->source }}
													&middot;
													<span class="font-semibold">{{ $this->conversation->query_string }}</span>
												</div>
											</div>
										@endif

										<div class="inline-flex items-start gap-2">
											<flux:icon name="globe-alt" variant="micro"/>
											<div>
												<span class="font-semibold">{{ $this->conversation->ip_address }}</span>
												&middot;
												<span class="hidden sm:inline">{{ $this->conversation->user_agent }}</span>
												<span class="inline sm:hidden">{{ str($this->conversation->user_agent)->limit(20) }}</span>
											</div>
										</div>

										@php
											$totalTokens = $this->conversation->messages->sum('total_tokens');
											$assistantCount = $this->conversation->messages->where('role', \App\Enums\ConversationRole::ASSISTANT)->count();
											$dislikedCount = $this->conversation->messages->where('disliked', true)->count();
										@endphp

										<div class="inline-flex items-start gap-2">
											<flux:icon name="cpu-chip" variant="micro"/>
											<div>
												<span class="font-semibold">{{ $totalTokens }}</span> Total AI Tokens Used In This Conversation
											</div>
										</div>

										<div class="inline-flex items-start gap-2">
											<flux:icon name="chat-bubble-left-right" variant="micro"/>
											<div>
												<span class="font-semibold">{{ $assistantCount }}</span> AI Responses
											</div>
										</div>

										<div class="inline-flex items-start gap-2">
											<flux:icon name="hand-thumb-down" variant="micro"/>
											<div>
												<span class="font-semibold">{{ $dislikedCount }}</span> Disliked Messages
											</div>
										</div>
									</div>
									<flux:spacer />

									<div class="flex flex-wrap gap-2">
										<flux:button type="button" icon="arrow-path" onclick="window.location.reload()" class="shrink-0">Refresh</flux:button>
										<flux:button variant="primary" icon="arrow-down-tray" tooltip="Coming soon" class="shrink-0">Export</flux:button>
									</div>
								</div>

								@foreach($this->conversation->messages as $message)
									<div @class([
											'flex max-w-[98%] sm:max-w-[94%]',
											'ml-auto justify-end' => $message->role->isUser(),
										])>

										<div @class([
												"relative min-w-0 break-words rounded-2xl shadow-lg p-2 sm:p-3",
												"bg-gray-100 dark:bg-zinc-700" => $message->role->isAssistant(),
												"bg-[#0059e1] dark:bg-[#0059e1] text-white dark:text-white" => $message->role->isUser(),
											])>

											<div class="flex-auto px-1 sm:px-3 py-1 sm:py-2">
												<div class="markdown-content text-sm sm:text-base" 
													data-content="{{ htmlspecialchars($message->message) }}"
													x-data
													x-markdown>
												</div>

												@if($message->role->isAssistant())
													<div class="flex items-center gap-2 mt-2">
														<flux:button size="xs" variant="primary" icon="arrow-path" class="text-xs sm:text-sm" wire:click="reviseAnswer({{ $message->id }})">Revise answer</flux:button>
														@if($message->disliked)
															<flux:badge size="xs" variant="pill" color="red" icon="hand-thumb-down" class="text-xs">
																<span>Disliked by user</span>
															</flux:badge>
														@endif
													</div>
												@endif
											</div>
										</div>
									</div>
								@endforeach
							</div>
						@else
							<div class="h-full flex items-center justify-center">
								<p class="text-sm">Select a conversation to view messages.</p>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</flux:card>


	<flux:modal name="revise-answer" class="w-full lg:max-w-2xl p-3 sm:p-6">
		<form wire:submit="store" class="space-y-4 sm:space-y-6">
			<div>
				<flux:heading size="lg" class="text-lg sm:text-xl">Revise answer</flux:heading>
				<flux:subheading class="text-xs sm:text-sm">
					Revise the answer to the user's message.
					@if(isset($assistant) && $assistant->disliked)
						<span class="text-red-500 font-semibold ml-1">This response was disliked by the user.</span>
					@endif
				</flux:subheading>
			</div>

			<flux:input label="User message" wire:model="revise.user" placeholder="User message" disabled/>
			
			<flux:textarea label="Assistant response" wire:model="revise.assistant" rows="3" placeholder="Assistant response" />
			
			<flux:textarea label="Expected response" wire:model="revise.expected" rows="3" placeholder="Expected response" />

			<div class="flex justify-end gap-2">
				<flux:button variant="ghost" x-on:click="$flux.modal('revise-answer').close()" class="text-xs sm:text-sm">Cancel</flux:button>
				<flux:button variant="primary" type="submit" class="text-xs sm:text-sm">Add to Your Data</flux:button>
			</div>
		</form>
	</flux:modal>
</div>
