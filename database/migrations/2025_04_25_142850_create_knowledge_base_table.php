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
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
			$table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('product_id')->nullable();
			$table->foreignId('knowledge_base_resource_id')->nullable();
			$table->text('question');
			$table->text('answer');
			$table->longText('embedding')->nullable();
            $table->timestamp('embedding_processed_at')->nullable();
			$table->float('similarity_score')->nullable();
			
			$table->unique(['question', 'workspace_id']);
			$table->timestamps();
        });


        Schema::create('knowledge_base_resources', function (Blueprint $table) {
			$table->id();
			$table->morphs('resourceable');
			$table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
			$table->unsignedInteger('words_count')->nullable();
			$table->unsignedInteger('characters_count')->nullable();
			$table->unsignedTinyInteger('status')->nullable();
			$table->text('error_message')->nullable();
			$table->timestamps();
			$table->timestamp('process_started_at')->nullable();
			$table->timestamp('process_completed_at')->nullable();
		});

        Schema::create('knowledge_base_url_resources', function (Blueprint $table) {
			$table->id();
			$table->string('url');
			$table->boolean('is_primary')->default(false);
			$table->tinyInteger('priority_score')->nullable();
			$table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
			$table->unique(['url', 'workspace_id']);
		});

		Schema::create('knowledge_base_file_resources', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('path');
			$table->string('type');
			$table->string('size');
		});

		Schema::create('knowledge_base_video_resources', function (Blueprint $table) {
			$table->id();
			$table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
			$table->string('url');
			$table->string('title')->nullable();
			$table->longText('content')->nullable();
			
			$table->unique(['url', 'workspace_id']);
		});

		Schema::create('knowledge_base_text_resources', function (Blueprint $table) {
			$table->id();
			$table->text('content');
		});

        Schema::create('knowledge_base_resource_duplicates', function (Blueprint $table) {
			$table->id();
			$table->foreignId('knowledge_base_id')->constrained()->cascadeOnDelete();
			$table->text('text');
		});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
