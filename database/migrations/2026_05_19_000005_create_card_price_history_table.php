<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per card per day — the tracked market value snapshot.
 * RefreshCardPrices appends a point on each daily run; the price
 * tracker charts plot these.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->decimal('market_price', 10, 2);
            $table->date('recorded_at');
            $table->timestamps();

            // At most one snapshot per card per day.
            $table->unique(['card_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_price_history');
    }
};
