<?php

namespace App\Providers;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

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
        Gate::before(fn (User $user): ?bool => $user->is_admin ? true : null);

        View::composer([
            'layouts.admin',
            'layouts.public',
            'auth.login',
            'auth.register',
            'pages.public.home',
            'pages.public.checkout',
            'pages.public.payment',
            'pages.admin.themes.index',
        ], function (ViewInstance $view): void {
            $storeSetting = StoreSetting::query()->find(1);

            $view->with([
                'storeLogoUrl' => $storeSetting?->logo_path === null ? null : Storage::disk('public')->url($storeSetting->logo_path),
                'storeName' => $storeSetting?->store_name ?? 'Apotek Aja',
            ]);
        });

        ResetPassword::toMailUsing(function (User $user, string $token): MailMessage {
            $storeName = StoreSetting::query()->whereKey(1)->value('store_name') ?? 'Apotek Aja';

            return (new MailMessage)
                ->subject('Reset password akun '.$storeName)
                ->greeting('Halo, '.$user->name.'!')
                ->line('Kami menerima permintaan reset password untuk akun '.($user->is_admin ? 'admin' : 'customer').' Anda di '.$storeName.'.')
                ->action('Reset password', route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]))
                ->line('Link ini berlaku selama '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' menit dan hanya dapat digunakan satu kali.')
                ->line('Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tetap aman.')
                ->salutation('Tim '.$storeName);
        });
    }
}
