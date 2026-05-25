<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            // Snapshot of who won + when they paid. Separated from
            // current_leader_id so a refund / loss of payment doesn't
            // disturb the bid history.
            $table->foreignId('winner_id')->nullable()->after('current_leader_id')
                ->constrained('users')->nullOnDelete();
            $table->decimal('winning_amount', 10, 2)->nullable()->after('winner_id');
            $table->timestamp('winner_paid_at')->nullable()->after('winning_amount');

            // none | requested | approved | rejected
            $table->string('refund_status', 16)->default('none')->after('winner_paid_at');
            $table->text('refund_reason')->nullable()->after('refund_status');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_reason');
            $table->timestamp('refund_resolved_at')->nullable()->after('refund_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_id');
            $table->dropColumn([
                'winning_amount',
                'winner_paid_at',
                'refund_status',
                'refund_reason',
                'refund_requested_at',
                'refund_resolved_at',
            ]);
        });
    }
};
