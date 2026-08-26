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
        Schema::create('daily_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
        
            $table->enum('day_type', ['rest', 'moderate', 'heavy'])->default('rest');
            $table->unsignedInteger('target_calories');

        
            $table->enum('status', ['draft', 'generated', 'completed'])->default('draft');
        

            $table->unsignedSmallInteger('target_protein_g')->nullable();
            $table->unsignedSmallInteger('target_carbs_g')->nullable();
            $table->unsignedSmallInteger('target_fat_g')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_plans');
    }
};
