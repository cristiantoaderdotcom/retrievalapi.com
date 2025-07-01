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
        Schema::create('processed_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_inbox_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->index();
            $table->string('subject')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->text('original_message');
            $table->text('ai_response')->nullable();
            $table->integer('total_tokens')->default(0);
            $table->boolean('was_replied')->default(false);
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            
            // Ensure we don't process the same email twice
            $table->unique(['email_inbox_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_emails');
    }
}; 