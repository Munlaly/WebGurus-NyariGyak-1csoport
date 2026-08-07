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
        Schema::create('user_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->float('amount_left')->nullable();
            $table->enum('status', ['FULL', 'OPENED', 'LOW'])->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->timestamp('last_audited_at')->nullable();
            $table->timestamps();

            // Egyedi indexek a gyorsabb kereséshez (ZeroWaste és Szinkronizáció)
            $table->index(['user_id', 'is_frozen', 'expiration_date'], 'idx_inventory_zerowaste');
            $table->index(['user_id', 'ingredient_id'], 'idx_inventory_sync');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_inventories');
    }
};
