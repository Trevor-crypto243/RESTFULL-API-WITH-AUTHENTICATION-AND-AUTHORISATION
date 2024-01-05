<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LoanApproved extends Notification
{
    use Queueable;

    private $amount_requested;
    private $product_name;
    private $outstanding_amount;
    private $amount_disbursable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($amount_requested, $amount_disbursable, $product_name, $outstanding_amount)
    {
        $this->amount_requested = $amount_requested;
        $this->product_name = $product_name;
        $this->outstanding_amount = $outstanding_amount;
        $this->amount_disbursable = $amount_disbursable;
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
            ->subject('Loan Approved')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your loan application for '.$this->product_name.' of KES '.number_format($this->amount_requested).' has been approved.')
            ->line("KES ".number_format($this->amount_disbursable).' has been deposited to your Quicksava wallet. Please login on the Quicksava app to access it')
            ->line("Your outstanding Quicksava wallet balance is KES ".number_format($this->outstanding_amount));
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
