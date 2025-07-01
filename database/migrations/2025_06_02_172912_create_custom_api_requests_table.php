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
        Schema::create('custom_api_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('custom_api_integration_id')->constrained('custom_api_integrations')->cascadeOnDelete();
            $table->json('request_data')->nullable(); // Data sent to the API
            $table->json('response_data')->nullable(); // Response received from the API
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('error_message')->nullable(); // Error message if request failed
            $table->integer('response_time')->nullable(); // Response time in milliseconds
            $table->integer('http_status_code')->nullable(); // HTTP status code received
            $table->text('raw_response')->nullable(); // Raw response for debugging
            $table->timestamps();
            
            $table->index(['conversation_id', 'status']);
            $table->index(['custom_api_integration_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_api_requests');
    }
};
