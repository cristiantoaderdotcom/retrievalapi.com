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
        Schema::create('custom_api_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name'); // User-friendly name for this API integration
            $table->text('description')->nullable(); // Description of what this API does
            $table->string('api_url'); // The API endpoint URL
            $table->string('http_method')->default('POST'); // GET, POST, PUT, DELETE
            $table->string('auth_type')->default('none'); // none, bearer, api_key, basic, custom
            $table->json('auth_config')->nullable(); // Authentication configuration
            $table->json('input_schema'); // Input fields and their configuration
            $table->json('trigger_keywords')->nullable(); // Keywords that trigger this API
            $table->json('ai_rules')->nullable(); // AI behavior rules
            $table->string('action_type'); // get_data, submit_data
            $table->boolean('is_active')->default(true); // Whether this integration is active
            $table->text('confirmation_message')->nullable(); // Message shown when API is triggered
            $table->json('success_response')->nullable(); // Success response configuration
            $table->json('headers')->nullable(); // Custom headers for the API request
            $table->integer('timeout')->default(30); // Request timeout in seconds
            $table->timestamps();
            
            $table->index(['workspace_id', 'is_active']);
            $table->index(['workspace_id', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_api_integrations');
    }
};
