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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->integer('default_shelf_life_days')->nullable();
            $table->integer('default_calories_per_100')->nullable();
            $table->float('default_protein')->nullable();
            $table->float('default_fat')->nullable();
            $table->float('default_carbs')->nullable();
            $table->timestamps();

            $table->index('parent_id', 'idx_categories_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
