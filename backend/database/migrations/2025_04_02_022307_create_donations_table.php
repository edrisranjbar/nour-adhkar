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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('reference_id')->unique();
            $table->string('card_pan')->nullable();
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
