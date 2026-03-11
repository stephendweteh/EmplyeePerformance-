<?php

namespace App\Notifications;

use App\Models\AppSetting;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Announcement $announcement)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $templates = AppSetting::emailAlertConfig();

        $subject = AppSetting::renderTemplate($templates['live_update_subject'], [
            ':title' => $this->announcement->title,
        ]);

        $body = AppSetting::renderTemplate($templates['live_update_body'], [
            ':title' => $this->announcement->title,
        ]);

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action($templates['live_update_action'], url('/announcements'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement_published',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
        ];
    }
}
