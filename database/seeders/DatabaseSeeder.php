<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		app()[PermissionRegistrar::class]->forgetCachedPermissions();
		Role::create(['name' => 'admin']);

		$user = User::create([
			'name' => 'Customer',
			'email' => 'adrian@devxart.ro',
			'password' => Hash::make('adrian@devxart.ro'),
			'standard' => true,
			'pro' => true,
			'messages_limit' => 200,
			'context_limit' => 250000,
			'email_verified_at' => now(),
		]);

		$user->assignRole('admin');

		$this->call([
			Timezones::class,
			Languages::class
		]);
	}
}
