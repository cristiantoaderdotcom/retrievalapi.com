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
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('workspace_id')->constrained('workspaces');
            $table->string('uuid')->unique();
            $table->string('page_id')->nullable();
            $table->string('page_name')->nullable();
            $table->string('page_access_token')->nullable();
            $table->string('page_verify_token')->nullable();
            $table->string('page_icon')->nullable();

            $table->boolean('handle_messages')->default(true);
            $table->boolean('handle_comments')->default(true);
            $table->dateTime('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_pages');
    }
};
