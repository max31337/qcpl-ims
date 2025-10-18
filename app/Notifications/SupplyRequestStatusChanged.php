<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\SupplyRequest;

class SupplyRequestStatusChanged extends Notification
{
    use Queueable;

    public $request;

    public function __construct(SupplyRequest $request)
    {
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Supply Request Status Updated')
            ->line('Your supply request status has changed to: ' . $this->request->status)
            ->action('View Request', url('/supplies/requests/' . $this->request->id))
            ->line('Thank you for using the system!');
    }
}
