<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            // Video the employee made for this topic (YouTube).
            $t->string('video_url', 500)->nullable()->after('link');
            $t->string('video_title', 255)->nullable()->after('video_url');

            // Payroll snapshot taken when the topic is completed, so later rate
            // changes never rewrite past earnings.
            $t->foreignId('completed_by')->nullable()->after('completed_at')
                ->constrained('employees')->nullOnDelete();
            $t->decimal('earned_amount', 10, 2)->default(0)->after('completed_by');

            $t->index(['completed_by', 'completed_at']);
            $t->unique('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            $t->dropUnique(['video_url']);
            $t->dropIndex(['completed_by', 'completed_at']);
            $t->dropConstrainedForeignId('completed_by');
            $t->dropColumn(['video_url', 'video_title', 'earned_amount']);
        });
    }
};
