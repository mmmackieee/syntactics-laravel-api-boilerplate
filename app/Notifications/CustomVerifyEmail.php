<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function toMail($notifiable)
    {
        $verifyUrl = $this->buildVerifyUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Click the button below to verify your email.')
            ->action('Verify Email', $verifyUrl)
            ->line('If you did not create an account, no further action is required.');
    }

    protected function buildVerifyUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.verification.verify', // This route name matches the renamed one above
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
