<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            // Set when an employee added this topic themselves (via their own
            // portal), so it can be tracked separately from admin-added topics.
            // Null means the topic came from an admin/the seed data.
            $t->foreignId('added_by')->nullable()->after('assigned_to')
                ->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            $t->dropConstrainedForeignId('added_by');
        });
    }
};
