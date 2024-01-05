<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewIdfApproval extends Notification
{
    use Queueable;
    private $business_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($business_name)
    {
        $this->business_name = $business_name;
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
            ->subject('IDF offer requested for '.$this->business_name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('A new invoice discount application has been submitted for your approval and sending of offer. Please log in to review send offer.')
            ->action('Review Applications',url('invoices'))
            ->line('Once you send the offer, the client will be able to accept it and funds will be available on their wallet.')
            ->line("\n");
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
