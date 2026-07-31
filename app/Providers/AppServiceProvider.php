<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Inject active announcements into all layouts and student-facing views
        View::composer(['layouts.app', 'layouts.admin', 'beranda'], function ($view) {
            $dismissed = session('dismissed_announcements', []);
            $announcements = Announcement::active()
                ->whereNotIn('id', $dismissed)
                ->orderByDesc('created_at')
                ->get();
            $view->with('announcements', $announcements);
        });
    }
}
