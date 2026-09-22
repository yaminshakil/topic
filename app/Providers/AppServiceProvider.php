<?php

namespace App\Providers;

use App\Models\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The employee sidebar's Channels dropdown needs the same channel list
        // wherever it's included (dashboard, topics, tracker-as-employee), so
        // it's shared here instead of threaded through every controller.
        View::composer('employee._nav', function ($view) {
            $employee = Auth::guard('employee')->user();

            $view->with('navChannels', $employee
                ? Channel::whereHas('topics', fn ($q) => $q->where('assigned_to', $employee->id))
                    ->orderBy('sort_order')->get()
                : collect());
        });
    }
}
