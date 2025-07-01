<?php

namespace App\Livewire\App\KnowledgeBase;

use App\Models\Workspace;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Models\KnowledgeBase as KnowledgeBaseModel;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Flux\Flux;

class KnowledgeBase extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Locked]
    public Workspace $workspace;

    public string $search = '';
    
    public array $form = [];
    
    public int $totalCount = 0;
    public int $trainedCount = 0;
    public int $processingCount = 0;
    public bool $allProcessed = false;

    public function mount(string $uuid)
    {
        $this->workspace = Workspace::where('uuid', $uuid)->firstOrFail();
        $this->updateStats();
    }
    
    public function updateStats(): void
    {
        $this->totalCount = KnowledgeBaseModel::where('workspace_id', $this->workspace->id)->count();
        $this->trainedCount = KnowledgeBaseModel::where('workspace_id', $this->workspace->id)
            ->whereNotNull('embedding_processed_at')
            ->count();
        $this->processingCount = $this->totalCount - $this->trainedCount;
        $this->allProcessed = $this->processingCount === 0 && $this->totalCount > 0;
    }
    
    public function checkAllProcessed(): void
    {
        $this->updateStats();
    }

    public function render()
    {
        $knowledgeBases = KnowledgeBaseModel::query()
            ->with('knowledgeBaseResource.resourceable')
            ->where('workspace_id', $this->workspace->id)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('question', 'like', '%' . $this->search . '%')
                        ->orWhere('answer', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        $knowledgeBases->getCollection()->each(function ($knowledgeBase) {
            $this->form[$knowledgeBase->id] = [
                'question' => $knowledgeBase->question,
                'answer' => $knowledgeBase->answer,
            ];
        });   


        return view('livewire.app.knowledge-base.knowledge-base', [
            'knowledgeBases' => $knowledgeBases,
        ])->extends('layouts.app')->section('main');
    }

    public function store(): void {
		$knowledgeBase = KnowledgeBaseModel::create([
			'workspace_id' => $this->workspace->id,
			'question' => '',
			'answer' => '',
		]);

		$this->form[$knowledgeBase->id] = [
			'question' => $knowledgeBase->question,
			'answer' => $knowledgeBase->answer,
		];
		
		$this->updateStats();

		Flux::toast(variant: 'success', text: 'Knowledge base added successfully.');
	}

	public function update($id): void {
		$this->validate([
			"form.$id.question" => 'required|string',
			"form.$id.answer" => 'nullable|string',
		]);

		$knowledgeBase = KnowledgeBaseModel::query()
			->where('workspace_id', $this->workspace->id)
			->findOrFail($id);

		if($knowledgeBase->question === data_get($this->form, $id . '.question')
			&& $knowledgeBase->answer === data_get($this->form, $id . '.answer')) {
			return;
		}

		$knowledgeBase->update([
			'question' => data_get($this->form, $id . '.question'),
			'answer' => data_get($this->form, $id . '.answer'),
			'embedding_processed_at' => null, // Reset processing status when content changes
		]);
		
		$this->updateStats();

		Flux::toast(variant: 'success', text: 'Knowledge base updated successfully.');
	}

	public function delete($id): void {
		$knowledgeBase = KnowledgeBaseModel::query()
			->where('workspace_id', $this->workspace->id)
			->findOrFail($id);

		unset($this->form[$id]);
		$knowledgeBase->delete();
		
		$this->updateStats();

		Flux::toast(variant: 'success', text: 'Knowledge base deleted successfully.');
	}

	public function destroy(): void {
		KnowledgeBaseModel::query()
			->where('workspace_id', $this->workspace->id)
			->delete();

		KnowledgeBaseModel::query()
			->where('workspace_id', $this->workspace->id)
			->update([
				'status' => null,
				'process_started_at' => null,
				'process_completed_at' => null,
			]);
			
		$this->updateStats();

		Flux::toast(variant: 'success', text: 'All knowledge base data deleted successfully.');
	}
}
