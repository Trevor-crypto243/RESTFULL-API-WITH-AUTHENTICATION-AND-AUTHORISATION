<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewIdfApplication extends Notification
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
            ->subject('New IDF application from '.$this->business_name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('A new invoice discount application has been submitted for review. Please log in to review and proceed with approvals.')
            ->action('Review Applications',url('invoices'))
            ->line('You will need to contact the respective accounts payables to confirm each attached invoice per application.')
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
