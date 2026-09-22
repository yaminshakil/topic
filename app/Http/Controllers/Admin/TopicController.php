<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        return view('admin.topics', [
            'channels' => Channel::orderBy('sort_order')->orderBy('id')->get(),
            'topics'   => Topic::ordered()->with('channel')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel_id' => ['required', 'exists:channels,id'],
            'title'      => ['required', 'string', 'max:255'],
            'category'   => ['nullable', 'string', 'max:100'],
            'link'       => ['nullable', 'url:http,https', 'max:500'],
        ], ['channel_id.required' => 'Channel and title are required.']);

        $data['category']   = trim($data['category'] ?? '') ?: 'Other';
        $data['link']       = $data['link'] ?? '';
        $data['sort_order'] = (int) Topic::where('channel_id', $data['channel_id'])->max('sort_order') + 10;

        Topic::create($data);

        return back()->with('ok', 'Topic added.');
    }

    public function update(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'link'     => ['nullable', 'url:http,https', 'max:500'],
        ]);

        $wantDone = $request->boolean('is_done');

        $topic->fill([
            'title'    => $data['title'],
            'category' => trim($data['category'] ?? '') ?: 'Other',
            'link'     => $data['link'] ?? '',
        ])->save();

        // Completion goes through the model so the payroll snapshot stays consistent.
        if ($wantDone && ! $topic->is_done) {
            $topic->markDone();
        } elseif (! $wantDone && $topic->is_done) {
            $topic->markUndone();
        }

        return back()->with('ok', 'Topic updated.');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return back()->with('ok', 'Topic deleted.');
    }
}
