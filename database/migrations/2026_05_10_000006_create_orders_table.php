<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique(); // e.g. PT-2026-000001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // pending | paid | shipped | delivered | cancelled
            $table->string('status', 16)->default('pending');
            // unpaid | paid | refunded | failed
            $table->string('payment_status', 16)->default('unpaid');
            $table->string('payment_method', 32)->nullable();
            $table->string('payment_reference')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Shipping snapshot
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone', 32)->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postal_code', 16)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('payment_status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->morphs('itemable');
            $table->string('name_snapshot');
            $table->string('image_snapshot')->nullable();
            $table->decimal('price_snapshot', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
