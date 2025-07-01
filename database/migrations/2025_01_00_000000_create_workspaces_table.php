<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->integer('language_id')->nullable();
            $table->unsignedInteger('daily_loads')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['uuid', 'user_id']);
        });

        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'key']);
        });

        Schema::create('workspace_loads', function (Blueprint $table) {
			$table->id();
			$table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
			$table->integer('loads')->default(0);
			$table->date('created_at');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
}; 