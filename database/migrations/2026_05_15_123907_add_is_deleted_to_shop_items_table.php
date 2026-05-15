<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_items', function (Blueprint $table) {
            if (! Schema::hasColumn('shop_items', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false)->after('is_active');
                $table->index('is_deleted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_items', function (Blueprint $table) {
            $table->dropIndex(['is_deleted']);
            $table->dropColumn('is_deleted');
        });
    }
};
