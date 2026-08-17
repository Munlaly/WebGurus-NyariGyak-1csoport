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
            $table->boolean('is_public')->default(false);
            $table->string('image')->nullable();

            $table->integer('calories')->nullable();
            $table->float('protein')->nullable();
            $table->float('fat')->nullable();
            $table->float('carbs')->nullable();

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
