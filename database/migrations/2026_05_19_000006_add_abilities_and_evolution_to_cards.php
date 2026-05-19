<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extra game data from pokemontcg.io that the card detail page
 * surfaces: Pokémon abilities and the evolution chain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->json('abilities')->nullable()->after('attacks');
            $table->string('evolves_from')->nullable()->after('flavor_text');
            $table->json('evolves_to')->nullable()->after('evolves_from');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['abilities', 'evolves_from', 'evolves_to']);
        });
    }
};
