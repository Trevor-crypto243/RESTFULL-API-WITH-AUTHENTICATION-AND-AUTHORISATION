<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewIdfAdminApproval extends Notification
{
    use Queueable;
    private $business_name;
    private $applicationId;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($business_name,$applicationId)
    {
        $this->business_name = $business_name;
        $this->applicationId = $applicationId;

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
            ->subject('Confirm IDF application from '.$this->business_name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('An IDF application has been forwarded for your confirmation before submitting for final approval. Please log in to review and proceed with approvals.')
            ->action('Review Application',url('invoices/details',$this->applicationId))
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
