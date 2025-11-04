<?php

namespace App\Notifications;

use App\Models\EmployeeLeave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Exception;

/**
 * @method attempts()
 */
class LeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $maxExceptions = 3;

    public function __construct(
        public readonly EmployeeLeave $leaveRequest
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->validateNotificationData();

        return (new MailMessage)
            ->subject('New Leave Request')
            ->line('A new leave request has been submitted.')
            ->line('Employee: ' . $this->leaveRequest->employee->name)
            ->line('Leave Date: ' . $this->leaveRequest->leave_date)
            ->line('Leave Type: ' . $this->leaveRequest->leave_type)
            ->line('Reason: ' . $this->leaveRequest->leave_reason)
            ->action('View Request', url('/leave-requests/' . $this->leaveRequest->id));
    }

    private function validateNotificationData(): void
    {
        if (!$this->leaveRequest->leave_type || !$this->leaveRequest->leave_reason) {
            throw new Exception('Required notification data missing');
        }

        if (!filter_var($this->leaveRequest->employee->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid employee email address');
        }
    }

    public function failed(Exception $exception): void
    {
        if ($this->attempts() >= $this->tries) {
            throw new Exception('Maximum retry attempts reached', 0, $exception);
        }
    }
}
