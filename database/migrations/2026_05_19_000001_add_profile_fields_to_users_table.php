<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public trainer-profile fields. A user's bio, social links and
 * visibility toggles are all edited from the settings page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('avatar');
            $table->string('location')->nullable()->after('bio');
            // { twitter, instagram, tiktok, youtube, discord, website }
            $table->json('social_links')->nullable()->after('location');
            // { show_collection, show_chase, show_socials, show_bio, allow_comments }
            $table->json('profile_settings')->nullable()->after('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'location', 'social_links', 'profile_settings']);
        });
    }
};
