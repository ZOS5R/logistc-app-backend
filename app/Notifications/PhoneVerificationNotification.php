<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PhoneVerificationNotification extends Notification
{
    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail']; // Change to 'sms' or your preferred method
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Your OTP code for phone verification is: ' . $this->otp)
                    ->action('Verify Phone', url('/'))
                    ->line('Thank you for using our application!');
    }
}
