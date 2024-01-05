<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EmployeeRejectd extends Notification
{
    use Queueable;

    private $reject_reason;
    private $employer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($reject_reason, $employer)
    {
        $this->reject_reason = $reject_reason;
        $this->employer = $employer;
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
            ->subject('Employee application rejected')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('We regret to inform you that your application as an employee at '.$this->employer.' has been rejected')
            ->line('See below the reason for your request rejection')
            ->line("\n")
            ->line(new HtmlString('<i>' . $this->reject_reason . '</i>'))
            ->line('If you believe this was a mistake, please contact your HR manager');
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
