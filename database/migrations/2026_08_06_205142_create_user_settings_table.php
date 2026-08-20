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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->json('goals')->nullable();
            $table->json('meal_plan_preference')->nullable();
            $table->string('household_size')->nullable();
            $table->string('prep_time_preference')->nullable();
            $table->integer('daily_calorie_target')->nullable();

            $table->json('custom_dislikes')->nullable();
            $table->enum('budget_or_comfort', ['budget_first', 'comfort_first'])->default('comfort_first');
            $table->json('system_preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
