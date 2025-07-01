<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Auth;
use Flux\Flux;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component {
	use WithPagination;

	public ?User $user;

	public $form = [
		'name' => '',
		'email' => '',
		'password' => '',
		'confirm_password' => '',
		'change_password' => false,
		'standard' => true,
		'pro' => false,
		'messages_limit' => 20,
		'context_limit' => 200000,
		'email_verified_at' => true,
		'status' => true,
	];

	public function mount() {
		$this->user = null;
	}

	public function render(Request $request) {
		$users = User::query()
			->with('roles')
			->paginate(10);

		$users->getCollection()->transform(function ($user) {
			$user->messages_limit = Number::abbreviate($user->messages_limit);
			$user->context_limit = Number::abbreviate($user->context_limit);

			return $user;
		});

		$roles = Role::all();

		return view('livewire.app.admin.users.index', compact('users', 'roles'))
			->extends('layouts.app')
			->section('main');
	}

	public function create() {
		$this->reset();

		$this->user = new User();
		

		$this->form = [
			'name' => '',
			'email' => '',
			'password' => '',
			'confirm_password' => '',
			'change_password' => true,
			'standard' => true,
			'pro' => false,
			'messages_limit' => 20,
			'context_limit' => 200000,
			'email_verified_at' => true,
			'status' => true,
		];

		$this->modal('user-edit')->show();
	}

	public function edit($id) {
		$this->user = User::query()
			->where('id', $id)
			->firstOrFail();

		$this->form = [
			'name' => $this->user->name,
			'email' => $this->user->email,
			'standard' => (bool) $this->user->standard,
			'pro' => (bool) $this->user->pro,

			'messages_limit' => $this->user->messages_limit,
			'context_limit' => $this->user->context_limit,

			'email_verified_at' => (bool) $this->user->email_verified_at,
			'status' => (bool) $this->user->status,
		];

		$this->modal('user-edit')->show();
	}

	public function save() {
		$this->validate([
			'form.name' => 'required|string|max:255',
			'form.email' => 'required|email',
			'form.password' => 'nullable|string|min:8',
			'form.confirm_password' => 'nullable|string|min:8|same:form.password',
			'form.standard' => 'required|boolean',
			'form.pro' => 'required|boolean',
			'form.messages_limit' => 'required|integer',
			'form.context_limit' => 'required|integer',
			'form.email_verified_at' => 'required|boolean',
			'form.status' => 'required|boolean',
		]);

		$existingUser = User::query()
			->whereNot('id', $this->user->id)
			->where('email', data_get($this->form, 'email'))
			->first();

		if ($existingUser) {
			$this->addError('form.email', 'The email has already been taken by another user.');
			return;
		}

		$this->user->name = data_get($this->form, 'name');
		$this->user->email = data_get($this->form, 'email');

		if (isset($this->form['change_password'])) {
			$this->user->password = Hash::make(data_get($this->form, 'password'));
		}

		$this->user->standard = data_get($this->form, 'standard');
		$this->user->pro = data_get($this->form, 'pro');
		$this->user->messages_limit = data_get($this->form, 'messages_limit');
		$this->user->context_limit = data_get($this->form, 'context_limit');
		$this->user->status = data_get($this->form, 'status');

		if(empty($this->user->id)) {
			$this->user->email_verified_at = isset($this->form['email_verified_at']) ? now() : null;
		}

		$this->user->save();

		$this->modal('user-edit')->close();
	}

	public function delete($id) {
		$user = User::query()
			->where('id', $id)
			->firstOrFail();

		$user->delete();
	}

	public function login($id) {
		$user = User::query()
			->where('id', $id)
			->firstOrFail();

		Auth::login($user);

		$this->redirect(route('app.index'));
	}
}
