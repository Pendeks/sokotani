<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->unsignedBigInteger('current_unit_price');
            $table->unsignedBigInteger('agreed_unit_price')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('fulfillment_method', 20);
            $table->text('fulfillment_notes')->nullable();
            $table->timestamps();
            $table->index(['buyer_id', 'status']);
            $table->index(['product_id', 'status']);
        });

        Schema::create('offer_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20);
            $table->unsignedBigInteger('unit_price')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_negotiations');
        Schema::dropIfExists('offers');
    }
};
