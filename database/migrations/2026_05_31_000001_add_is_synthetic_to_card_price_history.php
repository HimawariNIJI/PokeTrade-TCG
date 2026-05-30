<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks rows that came from the demo backfill seeder (random walk) so
 * the UI can distinguish them from real snapshots accruing daily via
 * RefreshCardPrices. Existing rows are treated as synthetic — at
 * migration time they all originate from the seeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_price_history', function (Blueprint $table) {
            $table->boolean('is_synthetic')->default(false)->after('market_price');
        });

        // Everything currently in the table came from the seeder.
        \DB::table('card_price_history')->update(['is_synthetic' => true]);
    }

    public function down(): void
    {
        Schema::table('card_price_history', function (Blueprint $table) {
            $table->dropColumn('is_synthetic');
        });
    }
};
