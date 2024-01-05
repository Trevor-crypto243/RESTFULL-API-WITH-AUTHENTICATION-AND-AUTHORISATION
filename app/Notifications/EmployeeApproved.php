<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EmployeeApproved extends Notification
{
    use Queueable;

    private $comments;
    private $employer;
    private $max_limit;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comments, $employer, $max_limit)
    {
        $this->comments = $comments;
        $this->employer = $employer;
        $this->max_limit = $max_limit;
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
            ->subject('Employee application approved')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Congratulations! Your application as an employee at '.$this->employer.' has been approved')
            ->line('Your salary advance limit is Ksh. '.number_format($this->max_limit))
            ->line('See below a comment from HR:')
            ->line("\n")
            ->line(new HtmlString('<i>' . $this->comments . '</i>'))
            ->line('Open your app and and apply for a loan under the Salary Advance module.');
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
