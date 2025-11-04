<?php

namespace App\Http\Action;

use App\Models\EmployeeLeave;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class LeaveRequestCreate
{
    public function __invoke(string $userId, string $leaveDate,string $leaveType,string $leaveReason): EmployeeLeave
    {
        DB::beginTransaction();
        try {
            $user = User::find($userId);

            if (!$user) {
                throw new Exception('User not found');
            }

            $existingRequest = EmployeeLeave::where('employee_id', $userId)
                ->where('leave_date', $leaveDate)
                ->exists();

            if ($existingRequest) {
                throw new Exception('Leave request already exists');
            }

            $leave = EmployeeLeave::create([
                'employee_id' => $userId,
                'leave_date' => $leaveDate,
                'leave_type' => $leaveType,
                'leave_reason' => $leaveReason,
                'leave_status' => 'pending'
            ]);

            DB::commit();
            return $leave;
        }
        catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
