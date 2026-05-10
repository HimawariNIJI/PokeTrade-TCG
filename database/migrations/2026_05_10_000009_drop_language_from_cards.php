<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Strict English-only marketplace — drop the per-card language column.
 * Per team decision on 2026-05-10, we sell/trade/track only English cards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            if (Schema::hasColumn('cards', 'language')) {
                $table->dropColumn('language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('language', 4)->default('en')->after('artist');
        });
    }
};
