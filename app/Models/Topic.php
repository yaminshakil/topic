<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Topic extends Model
{
    protected $fillable = [
        'channel_id', 'title', 'category', 'link', 'assigned_to', 'added_by',
        'sort_order', 'is_done', 'completed_at',
        'video_url', 'video_title', 'video_channel', 'video_published_at', 'completed_by', 'earned_amount',
    ];

    protected function casts(): array
    {
        return [
            'is_done'            => 'boolean',
            'completed_at'       => 'datetime',
            'earned_amount'      => 'decimal:2',
            'video_published_at' => 'date',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'completed_by');
    }

    /** The employee who added this topic themselves, if any (null = admin/seed). */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'added_by');
    }

    /** Standard display ordering: channel, category, sort order. */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->join('channels', 'channels.id', '=', 'topics.channel_id')
            ->select('topics.*')
            ->orderBy('channels.sort_order')
            ->orderBy('topics.category')
            ->orderBy('topics.sort_order')
            ->orderBy('topics.id');
    }

    /**
     * Mark done now. Snapshots who completed it and what it paid at this
     * moment (the assigned employee's rate for this channel).
     */
    public function markDone(): void
    {
        $rate = $this->assigned_to
            ? (float) Rate::where('channel_id', $this->channel_id)
                ->where('employee_id', $this->assigned_to)->value('amount')
            : 0.0;

        $this->forceFill([
            'is_done'       => true,
            'completed_at'  => now(),
            'completed_by'  => $this->assigned_to,
            'earned_amount' => $rate,
        ])->save();
    }

    /** Undo completion and remove the earning that came with it. */
    public function markUndone(): void
    {
        $this->forceFill([
            'is_done'       => false,
            'completed_at'  => null,
            'completed_by'  => null,
            'earned_amount' => 0,
        ])->save();
    }

    public function toggleDone(): void
    {
        $this->is_done ? $this->markUndone() : $this->markDone();
    }

    /** Deterministic category label for a topic title. */
    public static function categoryFor(string $title): string
    {
        $t = mb_strtolower($title);

        if (Str::contains($t, ['linux', 'ubuntu', 'debian', 'fedora', 'gpu', 'cuda', 'rocm',
            'ollama', 'comfyui', 'pytorch', 'tensorflow'])) {
            return 'Linux / GPU';
        }
        if (Str::contains($t, 'windows')) return 'Windows';
        if (Str::contains($t, 'mac')) return 'macOS';
        if (Str::contains($t, ['claude', 'gemini', 'gpt', 'astra', 'ai ', ' ai'])) {
            return 'AI Tools';
        }

        return 'Other';
    }
}
