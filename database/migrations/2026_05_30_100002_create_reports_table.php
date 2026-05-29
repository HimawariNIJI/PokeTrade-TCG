<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            // Polymorphic target: a forum thread, forum post, or profile comment.
            $table->morphs('reportable');
            $table->string('reason', 64);
            $table->text('details')->nullable();
            // open | resolved | dismissed
            $table->string('status', 16)->default('open');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            // A trainer can only file one open-or-resolved report per target.
            $table->unique(['reporter_id', 'reportable_type', 'reportable_id'], 'reports_reporter_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
