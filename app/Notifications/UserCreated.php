<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreated extends Notification
{
    use Queueable;

    private $password;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Quicksava Account Created')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your account on Quicksava has been created as a '.$notifiable->role->name.'.')
            ->line('Please use the following credentials to log in to your account. Make sure to change your password')
            ->line('Username/E-Mail: '.$notifiable->email)
            ->line('Password: '.$this->password)
            ->action('LOG IN NOW',url('login'))
            ->line('Feel free to contact our support team should you have any questions.')
            ->line('Welcome to Quicksava!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
