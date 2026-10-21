<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestRejected extends Notification
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'rejected',
            'title' => 'Application rejected',
            'message' => "Your application {$this->serviceRequest->reference_number} was rejected. Reason: {$this->serviceRequest->rejection_reason}",
            'url' => route('citizen.applications'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject('BarangayChain PH - Application Rejected')
            ->greeting("Hello {$notifiable->name},")
            ->line($data['message'])
            ->line('Please review the reason and submit a new application if appropriate.')
            ->action('View My Applications', $data['url']);
    }
}
