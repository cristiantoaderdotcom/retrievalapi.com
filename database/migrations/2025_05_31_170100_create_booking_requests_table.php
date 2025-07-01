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
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('booking_integration_id')->constrained('booking_integrations')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, completed, cancelled, failed
            $table->json('request_data')->nullable(); // Customer information and preferences
            $table->string('external_booking_id')->nullable(); // ID from the external booking platform
            $table->string('booking_url')->nullable(); // Direct URL to the booking or confirmation
            $table->text('notes')->nullable(); // Internal notes
            $table->timestamp('booking_date')->nullable(); // When the actual appointment is scheduled
            $table->timestamp('completed_at')->nullable(); // When the booking was completed
            $table->timestamps();
            
            $table->index(['conversation_id', 'status']);
            $table->index(['booking_integration_id', 'status']);
            $table->index('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
}; 