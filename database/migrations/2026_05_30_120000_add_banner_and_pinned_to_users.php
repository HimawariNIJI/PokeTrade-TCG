<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trainer profile customisation: a banner image strip on top of the
 * public profile, plus a list of "pinned" card IDs they want to show
 * off from their digital collection.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('banner')->nullable()->after('avatar');
            $table->json('pinned_cards')->nullable()->after('profile_settings');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['banner', 'pinned_cards']);
        });
    }
};
