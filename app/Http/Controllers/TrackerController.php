<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackerController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $channel = (string) $request->query('channel', 'all');
        $status  = (string) $request->query('status', 'all');

        $topics = Topic::ordered()
            ->with('channel')
            ->when($q !== '', fn ($query) => $query->where('topics.title', 'like', '%' . $q . '%'))
            ->when($channel !== 'all', fn ($query) => $query->where('channels.slug', $channel))
            ->when($status === 'done', fn ($query) => $query->where('topics.is_done', true))
            ->when($status === 'pending', fn ($query) => $query->where('topics.is_done', false))
            ->get();

        // Group by channel + category (section headers).
        $groups = $topics->groupBy(fn (Topic $t) => $t->channel_id . '|' . $t->category)
            ->map(fn ($items, $key) => [
                'key'      => sha1($key),
                'channel'  => $items->first()->channel,
                'category' => $items->first()->category,
                'items'    => $items,
            ]);

        return view('tracker.index', [
            'groups'   => $groups,
            'channels' => Channel::orderBy('sort_order')->get(),
            'stats'    => $this->stats(),
            'q'        => $q,
            'channel'  => $channel,
            'status'   => $status,
            'isAdmin'  => (bool) $request->session()->get('tracker_admin'),
            'employee' => Auth::guard('employee')->user(),
        ]);
    }

    public function toggle(Topic $topic): JsonResponse
    {
        $topic->toggleDone();

        return response()->json([
            'ok'      => true,
            'is_done' => $topic->is_done,
            'stats'   => $this->stats(),
        ]);
    }

    private function stats(): array
    {
        $total     = Topic::count();
        $done      = Topic::where('is_done', true)->count();

        return [
            'total'     => $total,
            'done'      => $done,
            'remaining' => $total - $done,
            'percent'   => $total ? (int) round($done / $total * 100) : 0,
        ];
    }
}
