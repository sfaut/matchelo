<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoie à un nouvel inscrit le mot de passe généré pour son compte.
 *
 * Envoi synchrone, volontairement sans ShouldQueue : mise en file, la notification
 * stockerait le mot de passe en clair dans la table des jobs.
 */
class AccountCreated extends Notification
{
    /**
     * @param  string  $password  mot de passe généré, en clair
     */
    public function __construct(
        private readonly string $password,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.account_created.subject'))
            ->greeting(__('notifications.account_created.greeting', ['first_name' => $notifiable->first_name]))
            ->line(__('notifications.account_created.intro'))
            ->line(__('notifications.account_created.password', ['password' => $this->password]))
            ->action(__('notifications.account_created.action'), route('login'))
            ->line(__('notifications.account_created.keep_safe'))
            ->salutation(__('notifications.account_created.salutation'));
    }
}
