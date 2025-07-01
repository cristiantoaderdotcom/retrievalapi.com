<div class="container @container mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:button icon="arrow-left" link="{{ route('app.email-inbox.index') }}" size="xs" variant="ghost" />
            <flux:heading size="lg">{{ $inbox->name }} Inbox</flux:heading>
        </div>
        <flux:badge variant="{{ $inbox->is_active ? 'success' : 'gray' }}">
            {{ $inbox->is_active ? 'Active' : 'Inactive' }}
        </flux:badge>
    </div>

    <div class="flex flex-col gap-4 @lg:flex-row">
        <div class="w-full @lg:w-1/3">
            <flux:card class="mb-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Filter Emails</flux:subheading>
                    <div class="flex gap-2">
                        <flux:button wire:click="setFilter('all')" size="xs" 
                            variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}">
                            All
                        </flux:button>
                        <flux:button wire:click="setFilter('replied')" size="xs" 
                            variant="{{ $filter === 'replied' ? 'primary' : 'ghost' }}">
                            Replied
                        </flux:button>
                        <flux:button wire:click="setFilter('not_replied')" size="xs" 
                            variant="{{ $filter === 'not_replied' ? 'primary' : 'ghost' }}">
                            Not Replied
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            <flux:card class="h-[calc(100vh-14rem)] overflow-auto">
                @forelse($emails as $email)
                    <div wire:key="email-{{ $email->id }}" 
                        wire:click="selectEmail({{ $email->id }})"
                        class="cursor-pointer border-b p-4 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800
                            {{ $selectedEmail && $selectedEmail->id === $email->id ? 'bg-zinc-100 dark:bg-zinc-900' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ $email->from_name }}</span>
                                <span class="text-sm text-zinc-500">{{ $email->from_email }}</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-xs text-zinc-500">{{ $email->created_at->format('M d, H:i') }}</span>
                                @if($email->was_replied)
                                    <flux:badge size="xs" variant="success">Replied</flux:badge>
                                @else
                                    <flux:badge size="xs" variant="gray">Not Replied</flux:badge>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="font-medium">{{ $email->subject }}</p>
                            <p class="line-clamp-2 text-xs text-zinc-500">
                                {{ Str::limit(strip_tags($email->original_message), 100) }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full flex-col items-center justify-center p-4">
                        <flux:icon name="envelope" class="size-12 text-zinc-300" />
                        <p class="mt-4 text-center text-zinc-500">No emails found</p>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $emails->links() }}
                </div>
            </flux:card>
        </div>

        <div class="w-full @lg:w-2/3">
            @if($selectedEmail)
                <flux:card class="h-[calc(100vh-10rem)] overflow-auto">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">{{ $selectedEmail->subject }}</flux:heading>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-sm">From: {{ $selectedEmail->from_name }} &lt;{{ $selectedEmail->from_email }}&gt;</span>
                                <span class="text-sm text-zinc-500">{{ $selectedEmail->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                        @if($selectedEmail->was_replied)
                            <flux:badge variant="success">Replied on {{ $selectedEmail->replied_at->format('M d, Y H:i') }}</flux:badge>
                        @else
                            <flux:badge variant="gray">Not Replied</flux:badge>
                        @endif
                    </div>

                    <flux:separator />
                    
                    <div class="mb-4 mt-4">
                        <flux:subheading>Email Content</flux:subheading>
                        <div class="mt-2 rounded-lg border p-4">
                            <div class="prose max-w-none dark:prose-invert">
                                {!! nl2br(e($selectedEmail->original_message)) !!}
                            </div>
                        </div>
                    </div>

                    @if($selectedEmail->was_replied)
                        <flux:separator />
                        
                        <div class="mt-4">
                            <flux:subheading>AI Response</flux:subheading>
                            <div class="mt-2 rounded-lg border p-4">
                                <div class="prose max-w-none dark:prose-invert">
                                    {!! nl2br(e($selectedEmail->ai_response)) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </flux:card>
            @else
                <flux:card class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center">
                    <flux:icon name="envelope-open" class="size-16 text-zinc-300" />
                    <p class="mt-4 text-center text-zinc-500">Select an email to view its content</p>
                </flux:card>
            @endif
        </div>
    </div>
</div>