<?php

namespace App\Livewire\App\Engagement;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Workspace;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Locked;
use Illuminate\Http\Request;

class Conversations extends Component
{
    
    #[Url] 
	public ?string $conversationUuid = null;

	public ?Conversation $conversation = null;

	public string $search = '';

	public ConversationMessage $assistant;

    #[Locked]
    public Workspace $workspace;

    public $revise = [
		'user' => '',
		'assistant' => '',
		'expected' => '',
	];

    public function mount($uuid) {

        $this->workspace = Workspace::query()
			->where('user_id', auth()->id())
            ->where('uuid', $uuid)
			->firstOrFail();

		if ($this->conversationUuid) {
			$this->show($this->conversationUuid);
		}
	}

    public function show($uuid) {
		$this->conversationUuid = $uuid;

		$this->conversation = Conversation::query()
			->with(['messages' => function($query) {
				$query->orderBy('id');
			}])
            ->where('workspace_id', $this->workspace->id)
			->where('uuid', $uuid)
			->first();

		$this->conversation->timestamps = false;
		$this->conversation->read_at = now();
		$this->conversation->save();
		
		// Emit an event to trigger markdown rendering in JavaScript
		$this->dispatch('conversationShown');
	}

    public function render(Request $request)
    {
        $conversations = Conversation::query()
			->withWhereHas('userMessage')
			->withWhereHas('assistantMessage')
			->withWhereHas('workspace', function ($query) {
				$query->where('user_id', auth()->id());
			})
			->when($this->search, function ($query) {
				$query->whereHas('messages', function ($query) {
					$query->where('message', 'like', '%' . $this->search . '%');
				});
			})
			->with(['messages' => function($query) {
				$query->select('id', 'conversation_id', 'role', 'disliked');
			}])
			->orderByDesc('created_at')
			->simplePaginate(15);

        return view('livewire.app.engagement.conversations', [
                'conversations' => $conversations
            ])
            ->extends('layouts.app')
            ->section('main');
    }
}
