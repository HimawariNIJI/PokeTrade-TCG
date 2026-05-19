<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A trainer's digital card collection. Each row is one card pulled from
 * gacha — duplicates are allowed (you can pull the same card twice).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            // how it was obtained: gacha (only source for now)
            $table->string('source', 16)->default('gacha');
            $table->timestamp('obtained_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_cards');
    }
};
