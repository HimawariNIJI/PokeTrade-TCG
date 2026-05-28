<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn([
                'refund_reason',
                'refund_requested_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->text('refund_reason')->nullable();

            $table->timestamp('refund_requested_at')
                ->nullable();
        });
    }
};