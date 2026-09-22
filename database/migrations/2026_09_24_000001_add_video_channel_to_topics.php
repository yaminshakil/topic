<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            // YouTube channel that uploaded the video, pulled from oEmbed (author_name).
            $t->string('video_channel', 255)->nullable()->after('video_title');
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $t) {
            $t->dropColumn('video_channel');
        });
    }
};
