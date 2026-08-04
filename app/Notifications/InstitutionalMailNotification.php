<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base notification that prefers institutional email via HasInstitutionalMail::routeNotificationForMail.
 */
abstract class InstitutionalMailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract protected function mailSubject(): string;

    abstract protected function mailLines(): array;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject($this->mailSubject());

        foreach ($this->mailLines() as $line) {
            $message->line($line);
        }

        return $message;
    }
}
