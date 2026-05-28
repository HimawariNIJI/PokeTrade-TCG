<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('starting_bid', 10, 2);
            $table->decimal('current_bid', 10, 2)->default(0);
            $table->decimal('bid_increment', 10, 2)->default(1);
            $table->decimal('buy_now_price', 10, 2)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            // scheduled | live | ended | cancelled
            $table->string('status', 16)->default('scheduled');
            $table->timestamps();

            $table->index(['status', 'ends_at']);
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->string('payment_status', 16)->default('pending');
            $table->string('midtrans_id')->nullable();
            $table->index(['auction_id', 'amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
        Schema::dropIfExists('auctions');
    }
};
