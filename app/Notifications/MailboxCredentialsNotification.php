<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * One-time mailbox credentials. Always delivered to the personal email address
 * (institutional inbox is not readable until the user has this password).
 */
class MailboxCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $institutionalEmail,
        public readonly string $plainPassword,
        public readonly string $webmailUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('بريدك المؤسسي في Leaders Academy')
            ->greeting('مرحباً،')
            ->line('تم إنشاء صندوق بريد مؤسسي لحسابك.')
            ->line('العنوان: '.$this->institutionalEmail)
            ->line('كلمة المرور المؤقتة: '.$this->plainPassword)
            ->line('يُفضّل تغيير كلمة المرور بعد أول دخول.')
            ->action('فتح WebMail', $this->webmailUrl)
            ->line('لا تشارك كلمة المرور مع أي شخص.');
    }
}
