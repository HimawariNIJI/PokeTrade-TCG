<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            // Polymorphic — points at either cards or shop_items
            $table->morphs('itemable');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_snapshot', 10, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'itemable_id', 'itemable_type'], 'cart_items_unique_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
