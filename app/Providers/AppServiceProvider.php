<?php

namespace App\Providers;

use App\Modules\Base\Infrastructure\Service\AccountManager;
use App\Modules\Base\Infrastructure\Service\Utilities;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Builder;

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
        Builder::defaultStringLength(150);

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            App::setLocale('es');

            // Intento directo desde la ruta
            $account = request()->route('account');

            // Si lo anterior devuelve null porque el router aún no procesó la ruta,
            // puedes obtenerlo por el segmento de la URL (ej: primer segmento)
            $account = $account ?? request()->segment(1);

            $account = Utilities::clearName($account);
            $frontend = '';
            if ($account == 'host') {
                $accountFromHost = AccountManager::getAccountFromHost(request());
                // $account = $accountFromHost->account;
                $frontend = $accountFromHost->host;
            } else {
                $frontend = env('SPA_WEBSITE') . '/' . $account;
            }

            $url = $frontend . '/recordar-password-formulario'
                . '?token=' . urlencode($token)
                . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

            return (new MailMessage)
                ->subject('Restablecer contraseña')
                ->line('Has recibido este correo porque se solicitó un restablecimiento de contraseña para tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Si no lo solicitaste, puedes ignorar este correo.');
        });
    }
}
