<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CitizenSubmissionReceived extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'info',
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
