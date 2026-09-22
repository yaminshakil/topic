<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Imports channels and topics from database/data/topics.php.
     * Safe to re-run: existing channels are kept, and topics are only
     * imported into channels that have none, so progress is never wiped.
     */
    public function run(): void
    {
        $data = require database_path('data/topics.php');

        foreach ($data['channels'] as $i => $c) {
            $channel = Channel::updateOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'icon' => $c['icon'], 'badge' => $c['badge'], 'sort_order' => $i]
            );

            if ($channel->topics()->exists()) {
                continue;
            }

            $now = now();
            $rows = [];
            foreach ($c['list'] as $j => $title) {
                $rows[] = [
                    'channel_id' => $channel->id,
                    'title'      => $title,
                    'category'   => Topic::categoryFor($title),
                    'sort_order' => $j,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 200) as $chunk) {
                Topic::insert($chunk);
            }
        }
    }
}
