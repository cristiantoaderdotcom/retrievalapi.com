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
        Schema::create('email_inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('workspace_id')->constrained('workspaces')->unique();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('encryption')->default('ssl');
            $table->boolean('validate_cert')->default(true);
            $table->string('username');
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->tinyInteger('failed_attempts')->default(0);
            $table->dateTime('start_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_inboxes');
    }
}; 