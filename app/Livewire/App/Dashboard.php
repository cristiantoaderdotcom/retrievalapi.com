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
		
	}

	public function render(Request $request) {
		return view('livewire.app.dashboard')
			->extends('layouts.app')
			->section('main');
	}
}
