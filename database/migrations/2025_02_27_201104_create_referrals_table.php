<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->integer('clicks')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(30.00);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            
            $table->unique(['user_id', 'referral_id']);
        });

        Schema::create('user_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_total', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('user_payments');
        Schema::dropIfExists('user_referrals');
        Schema::dropIfExists('referrals');
    }
};
