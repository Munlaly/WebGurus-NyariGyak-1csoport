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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Metabolic inputs
            $table->enum('sex', ['male', 'female']);
            $table->date('birthdate')->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->enum('baseline_activity', [
                'sedentary', 
                'lightly_active', 
                'moderately_active', 
                'very_active'
            ])->default('sedentary');
            $table->enum('fitness_goal', ['lose_weight', 'maintain', 'gain_muscle'])->default('maintain');

            // Computed targets cache
            $table->unsignedInteger('weekly_calorie_target')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
