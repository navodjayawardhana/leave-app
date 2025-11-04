<?php

use App\Http\Action\LeaveRequestCreate;
use App\Models\EmployeeLeave;
use App\Models\User;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveRequestCreate = new LeaveRequestCreate();
        $this->user = User::factory()->create();
        $this->leaveDate = '2024-04-10';
        $this->leaveType = 'annual';
        $this->leaveReason = 'Vacation';
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
}
