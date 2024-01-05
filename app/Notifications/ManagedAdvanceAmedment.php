<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ManagedAdvanceAmedment extends Notification
{
    use Queueable;
    private $comments;
    private $inuaApplicationId;
    private $applicant_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comments, $inuaApplicationId, $applicant_name)
    {
        $this->comments = $comments;
        $this->inuaApplicationId = $inuaApplicationId;
        $this->applicant_name = $applicant_name;
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
            ->subject('Managed Salary Advance application amendment ')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('The Salary Advance application you made for '.strtoupper($this->applicant_name).' needs an amendment. Please see the comments below from admin:')
            ->line(new HtmlString('<i>' . $this->comments . '</i>'))
            ->action('Review Application',url('advance/requests/details',$this->inuaApplicationId))
            ->line('Kindly follow the link above to review and update the application as necessary.')
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
