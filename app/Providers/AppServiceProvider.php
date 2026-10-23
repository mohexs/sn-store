<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(fn (User $user): ?bool => $user->is_admin ? true : null);

        ResetPassword::toMailUsing(function (User $user, string $token): MailMessage {
            return (new MailMessage)
                ->subject('Reset password akun SN Store')
                ->greeting('Halo, '.$user->name.'!')
                ->line('Kami menerima permintaan reset password untuk akun '.($user->is_admin ? 'admin' : 'customer').' Anda di SN Store.')
                ->action('Reset password', route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]))
                ->line('Link ini berlaku selama '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' menit dan hanya dapat digunakan satu kali.')
                ->line('Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tetap aman.')
                ->salutation('Tim SN Store');
        });
    }
}
