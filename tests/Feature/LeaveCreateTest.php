<?php

use App\Http\Action\LeaveRequestCreate;
use App\Models\EmployeeLeave;
use App\Models\User;
use App\Services\LeaveNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveCreateTest extends TestCase
{
    use RefreshDatabase;

    private LeaveRequestCreate $leaveRequestCreate;
    private User $user;
    private string $leaveDate;
    private string $leaveType;
    private string $leaveReason;
    private LeaveNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = Mockery::mock(LeaveNotificationService::class);
        $this->leaveRequestCreate = new LeaveRequestCreate($this->notificationService);
        $this->user = User::factory()->create();
        $this->leaveDate = '2024-04-10';
        $this->leaveType = 'annual';
        $this->leaveReason = 'Vacation';
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_should_throw_when_user_not_found(): void
    {
        $nonExistentUserId = '99999-9999-9999';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found');

        $this->leaveRequestCreate->__invoke(
            $nonExistentUserId,
            $this->leaveDate,
            $this->leaveType,
            $this->leaveReason
        );
    }

    public function test_should_throw_leave_request_already_exists(): void
    {
        EmployeeLeave::create([
            'employee_id' => $this->user->id,
            'leave_date' => $this->leaveDate,
            'leave_type' => $this->leaveType,
            'leave_reason' => $this->leaveReason,
            'leave_status' => 'pending'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Leave request already exists');

        $this->leaveRequestCreate->__invoke(
            $this->user->id,
            $this->leaveDate,
            $this->leaveType,
            $this->leaveReason
        );
    }

    public function test_should_create_leave_request_successfully(): void
    {
        $this->notificationService
            ->shouldReceive('sendLeaveRequestNotification')
            ->once()
            ->withArgs(function ($leaveRequest) {
                return $leaveRequest instanceof EmployeeLeave
                    && $leaveRequest->employee_id === $this->user->id
                    && $leaveRequest->leave_date === $this->leaveDate
                    && $leaveRequest->leave_type === $this->leaveType
                    && $leaveRequest->leave_reason === $this->leaveReason;
            });

        $result = $this->leaveRequestCreate->__invoke(
            $this->user->id,
            $this->leaveDate,
            $this->leaveType,
            $this->leaveReason
        );

        $this->assertInstanceOf(EmployeeLeave::class, $result);
        $this->assertEquals($this->user->id, $result->employee_id);
        $this->assertEquals($this->leaveDate, $result->leave_date);
        $this->assertEquals($this->leaveType, $result->leave_type);
        $this->assertEquals($this->leaveReason, $result->leave_reason);
        $this->assertEquals('pending', $result->leave_status);

        $this->assertDatabaseHas('employee_leave', [
            'employee_id' => $this->user->id,
            'leave_date' => $this->leaveDate,
            'leave_type' => $this->leaveType,
            'leave_reason' => $this->leaveReason,
            'leave_status' => 'pending'
        ]);
    }
}
