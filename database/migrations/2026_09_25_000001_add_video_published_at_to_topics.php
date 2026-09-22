<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            // The video's original upload date on YouTube, scraped from the watch page.
            $t->date('video_published_at')->nullable()->after('video_channel');
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            $t->dropColumn('video_published_at');
        });
    }
};
