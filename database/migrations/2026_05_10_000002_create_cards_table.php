<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('api_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('supertype', 32);
            $table->json('subtypes')->nullable();
            $table->string('hp', 16)->nullable();
            $table->json('types')->nullable();
            $table->string('rarity', 64)->nullable();
            $table->string('regulation_mark', 4)->nullable();
            $table->string('number', 16)->nullable();
            $table->string('set_id', 32)->default('sv8pt5');
            $table->string('set_name')->default('Prismatic Evolutions');
            $table->string('set_series')->default('Scarlet & Violet');
            $table->json('national_pokedex_numbers')->nullable();
            $table->string('image_small')->nullable();
            $table->string('image_large')->nullable();
            $table->json('attacks')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('resistances')->nullable();
            $table->json('retreat_cost')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('artist')->nullable();
            $table->string('language', 4)->default('en');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('market_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->index(['supertype', 'rarity']);
            $table->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
