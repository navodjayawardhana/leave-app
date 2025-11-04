<?php

use App\Models\EmployeeLeave;
use App\Models\User;
use App\Notifications\LeaveRequestNotification;
use App\Services\LeaveNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeaveNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $admin;
    private EmployeeLeave $leaveRequest;
    private LeaveNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'email' => 'sampleemployee@gmail.com',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@gmail.com',
        ]);

        $this->leaveRequest = EmployeeLeave::create([
            'employee_id' => $this->employee->id,
            'leave_date' => '2024-04-10',
            'leave_type' => 'annual',
            'leave_reason' => 'Vacation',
            'leave_status' => 'pending'
        ]);

        $this->notificationService = new LeaveNotificationService();
    }

    public function test_notification_sent_successfully(): void
    {
        $this->notificationService->sendLeaveRequestNotification($this->leaveRequest);

        Notification::assertSentTo(
            $this->admin,
            LeaveRequestNotification::class,
            function ($notification) {
                return $notification->leaveRequest->id === $this->leaveRequest->id;
            }
        );
    }

    public function test_notification_sent_to_multiple_admins(): void
    {
        $secondAdmin = User::factory()->create([
            'email' => 'admin2@gmail.com',
            'role' => 'admin'
        ]);

        $this->notificationService->sendLeaveRequestNotification($this->leaveRequest);

        Notification::assertSentTo([$this->admin, $secondAdmin], LeaveRequestNotification::class);
    }

    public function test_notification_fails_when_admin_email_invalid(): void
    {
        $this->admin->email = 'invalid-email';
        $this->admin->save();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid admin email address');

        $this->notificationService->sendLeaveRequestNotification($this->leaveRequest);
    }
}
