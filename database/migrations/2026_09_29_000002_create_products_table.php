<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 80);
            $table->text('description');
            $table->string('unit', 40);
            $table->decimal('available_quantity', 12, 2);
            $table->decimal('minimum_quantity', 12, 2);
            $table->unsignedBigInteger('asking_price_per_unit');
            $table->string('location');
            $table->string('image_path')->nullable();
            $table->date('harvest_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
