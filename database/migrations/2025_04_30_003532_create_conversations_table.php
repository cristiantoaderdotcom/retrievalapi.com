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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
			$table->uuid()->unique();
            $table->string('type')->nullable();
            $table->string('type_source')->nullable();
			$table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
			$table->ipAddress()->nullable();
			$table->string('user_agent')->nullable();
			$table->string('source')->nullable();
			$table->string('query_string')->nullable();

            $table->index('workspace_id', 'uuid');

			$table->timestamp('read_at')->nullable();
			$table->timestamps();
        });


        Schema::create('conversation_messages', function (Blueprint $table) {
			$table->id();
			$table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
			$table->unsignedTinyInteger('role');
			$table->text('message');
			$table->float('score')->nullable();
			$table->integer('total_tokens')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('disliked')->default(false);

            $table->index('conversation_id', 'role');
            $table->timestamp('revised_at')->nullable();
			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
