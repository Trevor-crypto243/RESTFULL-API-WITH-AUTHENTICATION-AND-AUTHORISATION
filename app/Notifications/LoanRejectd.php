<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LoanRejectd extends Notification
{
    use Queueable;

    private $reject_reason;
    private $amount_requested;
    private $product_name;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($reject_reason, $amount_requested, $product_name)
    {
        $this->reject_reason = $reject_reason;
        $this->amount_requested = $amount_requested;
        $this->product_name = $product_name;

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
            ->subject('Loan rejected')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('We regret to inform you that your application for '.$this->product_name.' of KES '.number_format($this->amount_requested).' has been rejected')
            ->line('See below the reason for your request rejection')
            ->line("\n")
            ->line(new HtmlString('<i>' . $this->reject_reason . '</i>'));
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
