<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Topic;

class DashboardController extends Controller
{
    public function index()
    {
        $thisStart = now()->startOfMonth();
        $thisEnd   = now()->endOfMonth();
        $lastStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastEnd   = now()->subMonthNoOverflow()->endOfMonth();

        $totalTopics = Topic::count();
        $doneTopics  = Topic::where('is_done', true)->count();

        $earnedThis = (float) Topic::where('is_done', true)
            ->whereBetween('completed_at', [$thisStart, $thisEnd])->sum('earned_amount');
        $earnedLast = (float) Topic::where('is_done', true)
            ->whereBetween('completed_at', [$lastStart, $lastEnd])->sum('earned_amount');

        $doneThisMonth = Topic::where('is_done', true)
            ->whereBetween('completed_at', [$thisStart, $thisEnd])->count();
        $doneLastMonth = Topic::where('is_done', true)
            ->whereBetween('completed_at', [$lastStart, $lastEnd])->count();

        $stats = [
            'employees' => [
                'value'    => Employee::count(),
                'sub'      => Employee::where('is_active', true)->count() . ' active',
                'icon'     => '👥',
                'tone'     => 'violet',
            ],
            'earned' => [
                'value'    => $earnedThis,
                'sub'      => $this->deltaLabel($earnedThis, $earnedLast, 'from last month'),
                'trend'    => $this->deltaDirection($earnedThis, $earnedLast),
                'icon'     => '💰',
                'tone'     => 'blue',
            ],
            'completed' => [
                'value'    => $doneThisMonth,
                'sub'      => $this->countDeltaLabel($doneThisMonth, $doneLastMonth),
                'trend'    => $this->deltaDirection($doneThisMonth, $doneLastMonth),
                'icon'     => '✅',
                'tone'     => 'teal',
            ],
            'rate' => [
                'value'    => $totalTopics ? round($doneTopics / $totalTopics * 100) : 0,
                'sub'      => ($totalTopics - $doneTopics) . ' topics remaining',
                'icon'     => '📈',
                'tone'     => 'amber',
            ],
        ];

        $recent = Topic::with(['channel', 'employee', 'completer'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }

    private function deltaDirection(float $now, float $prev): string
    {
        return $now > $prev ? 'up' : ($now < $prev ? 'down' : 'flat');
    }

    private function deltaLabel(float $now, float $prev, string $suffix): string
    {
        if ($prev <= 0) {
            return $now > 0 ? "new this month" : "no change {$suffix}";
        }

        $pct = round((($now - $prev) / $prev) * 100, 1);

        return ($pct >= 0 ? '+' : '') . $pct . "% {$suffix}";
    }

    private function countDeltaLabel(int $now, int $prev): string
    {
        $diff = $now - $prev;
        if ($diff === 0) {
            return 'same as last month';
        }

        return ($diff > 0 ? '+' : '') . $diff . ' vs last month';
    }
}
