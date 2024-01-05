<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $msisdn;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$email,$msisdn)
    {
        $this->name = $name;
        $this->email = $email;
        $this->msisdn = $msisdn;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
   

     public function build()
     {
         $message = "Welcome to My Website\n\n";
         $message .= "Hello {$this->name},\n";
         $message .= "Thank you for signing up on our website. We're excited to have you as a member!\n";
         $message .= "Your email: {$this->email}\n";
         $message .= "Your MSISDN: {$this->msisdn}\n";
         $message .= "Feel free to explore our website and enjoy all the features we have to offer.\n";
         $message .= "If you have any questions or need assistance, don't hesitate to contact us.\n";
         $message .= "Best regards, Your Website Team";
 
        return $this->subject('Welcome to My Website')
            ->text($message);
     }
}
