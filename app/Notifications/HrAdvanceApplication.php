<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HrAdvanceApplication extends Notification
{
    use Queueable;
    private $employee_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($employee_name)
    {
        $this->employee_name = $employee_name;
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
            ->subject('New Loan approval request for '.$this->employee_name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('A new Salary Advance Loan request has been submitted for your approval by Quicksava. Please log in to review and action appropriately.')
            ->action('Review Applications',url('hr/advance/pending'))
            ->line('Once you approve the request, Quicksava will be able to send a loan offer to the mentioned employee.')
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
