<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Register extends Component {
	public string $name = '';

	public string $email = '';

	public string $password = '';
	public string $password_confirmation = '';

	public function store() {
		$this->validate([
			'name' => ['required'],
			'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
			'password' => ['required', 'confirmed', Rules\Password::defaults()],
		]);

		$user = User::create([
			'email' => $this->email,
			'name' => $this->name,
			'password' => Hash::make($this->password),
		]);

		event(new Registered($user));

		$defaultName = 'Customer';
		$processedName = $user->name;

		try {
			$response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Kit-Api-Key' => config('services.kit.api_key') 
            ])->post('https://api.kit.com/v4/subscribers', [
                'first_name' => !empty($processedName)                                    
                                 ? (Str::contains($processedName, ' ')   
                                    ? Str::before($processedName, ' ') 
                                    : $processedName)
                                 : $defaultName,
                'email_address' => $user->email,
                'state' => 'active',
                'fields' => [
                    'Last name' => !empty($processedName) && Str::contains($processedName, ' ') 
                                   ? Str::afterLast($processedName, ' ')                     
                                   : $defaultName,                                           
                    'Birthday' => '-',
                    'Source' => 'https://app.replyelf.com/register',
                ]
            ]);
		} catch (\Exception $e) {
			Log::error('Kit API Error: ' . $e->getMessage());
		}

		Auth::login($user, true);

		return redirect()->intended(route('app.index', absolute: false));
	}

	public function render() {
		return view('livewire.auth.register')
			->extends('layouts.auth')
			->section('main');
	}
}
