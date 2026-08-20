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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->integer('prep_time_minutes')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('image')->nullable();

            $table->unsignedInteger('calories')->nullable();
            $table->decimal('protein')->nullable();
            $table->decimal('fat')->nullable();
            $table->decimal('carbs')->nullable();
           
            $table->json('meal_types')->nullable();

            $table->unsignedTinyInteger('servings')->default(1);

            $table->unsignedTinyInteger('servings')->default(1);

            $table->timestamps();

            $table->index('user_id', 'idx_recipes_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
