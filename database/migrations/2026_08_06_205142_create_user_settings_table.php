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

            $table->json('goal')->nullable();
            $table->json('meal_plan_preferences')->nullable();
            $table->json('household_size')->nullable();
            $table->json('prep_time_prefference')->nullable();
            $table->enum('budget_or_comfort', ['budget_first', 'comfort_first'])->default('comfort_first');
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
