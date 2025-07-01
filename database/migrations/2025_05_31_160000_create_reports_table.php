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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('report_type'); // bug_report, abuse_report, security_issue, etc.
            $table->text('report_content'); // The actual report content from user
            $table->string('status')->default('pending'); // pending, investigating, resolved, dismissed
            $table->text('notes')->nullable(); // Internal notes for admin
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('report_type');
            $table->index('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}; 