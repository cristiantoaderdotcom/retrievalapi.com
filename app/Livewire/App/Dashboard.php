<?php

namespace App\Livewire\App;

use App\Models\EmailInbox;
use App\Models\ProcessedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Livewire\Component;

class Dashboard extends Component {
	public $data = [];

	public array $stats = [
		'inboxes' => 0,
		'messages_limit' => 0,
		'context_limit' => 0,
		'total_emails' => 0,
		'total_replies' => 0,
		'reply_rate' => 0,
	];

	public function mount(Request $request) {
		$cacheKey = 'dashboard_stats_' . $request->user()->id;
		$this->data = cache()->remember($cacheKey, 600, function() use ($request) {
			$this->stats['inboxes'] = EmailInbox::query()->where('user_id', $request->user()->id)->count();
			$this->stats['messages_limit'] = Number::abbreviate($request->user()->messages_limit);
			$this->stats['context_limit'] = Number::abbreviate($request->user()->context_limit);

			$inboxIds = EmailInbox::query()
				->where('user_id', $request->user()->id)
				->pluck('id');
		
			$this->stats['total_emails'] = ProcessedEmail::query()
				->whereIn('email_inbox_id', $inboxIds)
				->count();
			
			$this->stats['total_replies'] = ProcessedEmail::query()
				->whereIn('email_inbox_id', $inboxIds)
				->where('was_replied', true)
				->count();
			
			$this->stats['reply_rate'] = $this->stats['total_emails'] > 0 
				? round(($this->stats['total_replies'] / $this->stats['total_emails']) * 100, 2) 
				: 0;

			$stats = [];
			for ($i = 29; $i >= 0; $i--) {
				$date = Carbon::now()->subDays($i)->format('Y-m-d');
				$startOfDay = Carbon::parse($date)->startOfDay();
				$endOfDay = Carbon::parse($date)->endOfDay();
				
				$emails = ProcessedEmail::query()
					->whereIn('email_inbox_id', $inboxIds)
					->whereDate('created_at', $date)
					->count();
				
				$replies = ProcessedEmail::query()
					->whereIn('email_inbox_id', $inboxIds)
					->where('was_replied', true)
					->whereBetween('created_at', [$startOfDay, $endOfDay])
					->count();
				
				$stats[] = [
					'date' => $date,
					'emails' => $emails ?? 0,
					'replies' => $replies ?? 0,
				];
			}
			
			return $stats;
		});

		$shouldRedirect = !$request->user()->pro && ($request->user()->last_pro_reminder_at === null || Carbon::parse($request->user()->last_pro_reminder_at)->lt(Carbon::now()->subDays(5)));
		if ($shouldRedirect) {
			$request->user()->update([
				'last_pro_reminder_at' => now(),
			]);

			return redirect()->route('app.account.plans');
		}
	}

	public function render(Request $request) {
		return view('livewire.app.dashboard')
			->extends('layouts.app')
			->section('main');
	}
}
