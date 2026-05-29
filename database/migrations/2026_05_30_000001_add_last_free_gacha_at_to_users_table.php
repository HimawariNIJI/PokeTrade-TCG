<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks when a trainer last claimed their free daily gacha pull. The
 * first pull of each calendar day is free; later pulls cost points
 * earned from the merch store.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_free_gacha_at')->nullable()->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_free_gacha_at');
        });
    }
};
