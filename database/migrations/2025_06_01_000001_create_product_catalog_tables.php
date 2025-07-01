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

        Schema::create('product_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('provider')->default('shopify');
            $table->unsignedInteger('scan_frequency')->default(10080);
            $table->timestamp('last_processed_at')->nullable();
            $table->unsignedTinyInteger('status')->default(0); 
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'url']);
        });


        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_feed_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->index();
            $table->string('title');
            $table->string('handle')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('external_created_at')->nullable();
            $table->timestamp('external_updated_at')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->json('tags')->nullable();
            $table->longText('embedding')->nullable();
            $table->timestamp('embedding_processed_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'external_id']);
        });


        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->index();
            $table->string('title');
            $table->string('option1')->nullable();
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('taxable')->default(true);
            $table->boolean('available')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('grams')->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->integer('position')->nullable();
            $table->timestamp('external_created_at')->nullable();
            $table->timestamp('external_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'external_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->index();
            $table->integer('position')->nullable();
            $table->string('src');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamp('external_created_at')->nullable();
            $table->timestamp('external_updated_at')->nullable();
            $table->json('variant_ids')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'external_id']);
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('position');
            $table->json('values');
            $table->timestamps();

            $table->unique(['product_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_feeds');
    }
}; 