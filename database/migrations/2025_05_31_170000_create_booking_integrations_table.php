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
        Schema::create('booking_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('platform'); // calendly, cal_com, google_calendar, custom, etc.
            $table->string('name'); // User-friendly name for this integration
            $table->string('status')->default('active'); // active, inactive, error
            $table->json('configuration'); // Platform-specific configuration (URLs, API keys, etc.)
            $table->json('trigger_keywords')->nullable(); // Keywords that trigger this booking integration
            $table->text('confirmation_message')->nullable(); // Message shown when booking is triggered
            $table->text('ai_instructions')->nullable(); // Instructions for AI on how to handle this booking
            $table->boolean('is_default')->default(false); // Whether this is the default booking integration
            $table->integer('priority')->default(0); // Priority order for multiple integrations
            $table->timestamps();
            
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_integrations');
    }
}; 