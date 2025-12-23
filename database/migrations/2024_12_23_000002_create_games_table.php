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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->unique();
            $table->string('title', 255);
            $table->string('image', 500);
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->integer('discount')->default(0);
            $table->string('platform', 50);
            $table->string('region', 20)->default('GLOBAL');
            $table->string('product_type', 50)->default('Game');
            $table->boolean('has_cashback')->default(false);
            $table->integer('cashback_percent')->default(0);
            $table->date('release_date')->nullable();
            $table->string('developer', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('popularity_score')->default(0);
            $table->timestamps();

            // Indexes for filtering and sorting
            $table->index('platform');
            $table->index('product_type');
            $table->index('price');
            $table->index('discount');
            $table->index('popularity_score');
            $table->index('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
