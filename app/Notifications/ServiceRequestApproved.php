<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestApproved extends Notification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'verified',
            'title' => 'Application approved',
            'message' => "Your application {$this->serviceRequest->reference_number} has been approved.",
            'url' => route('citizen.documents'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject('BarangayChain PH - Application Approved')
            ->greeting("Hello {$notifiable->name},")
            ->line($data['message'])
            ->line('You can now view your approved application in the citizen portal.')
            ->action('View My Documents', $data['url']);
    }
}
