<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Grandfathers existing accounts as verified so the User model's new
    // `implements MustVerifyEmail` (and the `verified` middleware added in
    // 046f311) doesn't lock them out on their next request. Future
    // registrations stay null until the OTP flow calls markEmailAsVerified.
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        // No-op: we can't tell which rows were originally null vs which
        // verified naturally before this migration ran. Rolling back would
        // re-lock legitimately-verified users.
    }
};
