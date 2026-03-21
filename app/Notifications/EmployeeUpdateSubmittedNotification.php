<?php

namespace App\Notifications;

use App\Models\AppSetting;
use App\Models\EmployeeUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeUpdateSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly EmployeeUpdate $update) {}

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
        $employee = $this->update->user;
        $templates = AppSetting::emailAlertConfig();

        $subject = AppSetting::renderTemplate($templates['employee_update_submitted_subject'], [
            ':employee_name' => $employee->name,
            ':date' => $this->update->date->toDateString(),
            ':team' => $employee->team?->name ?? 'No team',
        ]);

        $body = AppSetting::renderTemplate($templates['employee_update_submitted_body'], [
            ':employee_name' => $employee->name,
            ':date' => $this->update->date->toDateString(),
            ':team' => $employee->team?->name ?? 'No team',
        ]);

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action($templates['employee_update_submitted_action'], url('/employer/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'employee_update_submitted',
            'employee_update_id' => $this->update->id,
            'employee_id' => $this->update->user_id,
            'employee_name' => $this->update->user->name,
            'date' => $this->update->date->toDateString(),
        ];
    }
}
