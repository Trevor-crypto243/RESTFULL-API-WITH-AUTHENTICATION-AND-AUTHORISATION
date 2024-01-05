<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LogbookSubmittedForApproval extends Notification
{
    use Queueable;

    private $applicant_name;
    private $applicationId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($applicant_name,$applicationId)
    {
        $this->applicant_name = $applicant_name;
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
            ->subject('Approval request for logbook loan application from '.$this->applicant_name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('A new logbook loan application approval request from '.strtoupper($this->applicant_name).' has been submitted. Please log in to review and proceed with approval.')
            ->action('Review Application',url('auto/applications',$this->applicationId))
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
