<?php

namespace App\Notifications;

use App\Models\AppSetting;
use App\Models\UpdateReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpdateReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly UpdateReview $review) {}

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

        $subject = AppSetting::renderTemplate($templates['update_reviewed_subject'], [
            ':rating' => (string) $this->review->rating,
            ':status' => str_replace('_', ' ', $this->review->status),
            ':comment' => $this->review->comment ?: 'No comment',
        ]);

        $body = AppSetting::renderTemplate($templates['update_reviewed_body'], [
            ':rating' => (string) $this->review->rating,
            ':status' => str_replace('_', ' ', $this->review->status),
            ':comment' => $this->review->comment ?: 'No comment',
        ]);

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action($templates['update_reviewed_action'], url('/employee/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'update_reviewed',
            'employee_update_id' => $this->review->employee_update_id,
            'review_id' => $this->review->id,
            'rating' => $this->review->rating,
            'status' => $this->review->status,
            'comment' => $this->review->comment,
        ];
    }
}
