<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendPasswordResetCodeEmail extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $token;
    private $code;

    /**
     * Create a new notification instance.
     *
     * @param $token
     */
    public function __construct($token, $code)
    {
        //
        $this->token = $token;
        $this->code  = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Reset Password")
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->line('If '.env('APP_NAME', 'Beadly').' asks you for a password reset code, enter: '.$this->code)
            ->line('Otherwise, you can click the button below to reset your password.')
            ->action('Reset Password', url(config('app.url').route('password.reset', $this->token, false)))
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
