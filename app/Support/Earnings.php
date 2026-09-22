<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/** Aggregates completed topics (completed_at + earned_amount) into day/week/month/year figures. */
class Earnings
{
    /** Today / this week (Mon–Sun) / this month / this year / all time. */
    public static function summary(Collection $rows): array
    {
        $now = now();
        $periods = [
            'today' => $now->copy()->startOfDay(),
            'week'  => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'year'  => $now->copy()->startOfYear(),
            'all'   => null,
        ];

        $out = [];
        foreach ($periods as $name => $from) {
            $in = $from ? $rows->filter(fn ($r) => $r->completed_at >= $from) : $rows;
            $out[$name] = ['count' => $in->count(), 'amount' => (float) $in->sum('earned_amount')];
        }

        return $out;
    }

    /**
     * Grouped history, newest first.
     *
     * @param  'day'|'week'|'month'|'year'  $period
     * @return list<array{label: string, count: int, amount: float}>
     */
    public static function breakdown(Collection $rows, string $period, int $limit = 0): array
    {
        $key = fn (CarbonInterface $d) => match ($period) {
            'day'   => $d->format('Y-m-d'),
            'week'  => $d->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $d->format('Y-m'),
            'year'  => $d->format('Y'),
        };
        $label = fn (CarbonInterface $d) => match ($period) {
            'day'   => $d->format('D, j M Y'),
            'week'  => $d->copy()->startOfWeek()->format('j M') . ' – ' . $d->copy()->endOfWeek()->format('j M Y'),
            'month' => $d->format('F Y'),
            'year'  => $d->format('Y'),
        };

        $groups = $rows->groupBy(fn ($r) => $key($r->completed_at))->sortKeysDesc();
        if ($limit > 0) {
            $groups = $groups->take($limit);
        }

        return $groups->map(fn ($items) => [
            'label'  => $label($items->first()->completed_at),
            'count'  => $items->count(),
            'amount' => (float) $items->sum('earned_amount'),
        ])->values()->all();
    }
}
