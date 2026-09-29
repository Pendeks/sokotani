<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable();
            $table->string('farm_location', 160)->nullable();
            $table->text('farm_description')->nullable();
            $table->boolean('is_verified')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_verified']);
            $table->dropColumn(['phone', 'farm_location', 'farm_description', 'is_verified']);
        });
    }
};
