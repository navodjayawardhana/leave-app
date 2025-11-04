<?php

namespace App\Services;

use App\Models\EmployeeLeave;
use App\Models\User;
use App\Notifications\LeaveRequestNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class LeaveNotificationService
{
    public function sendLeaveRequestNotification(EmployeeLeave $leaveRequest): void
    {
        try {
            $admins = User::where('role','admin')->get();

            if ($admins->isEmpty()) {
                throw new Exception('No admin users found to notify');
            }

            foreach ($admins as $admin) {
                if (!filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid admin email address');
                }

                $admin->notify(new LeaveRequestNotification($leaveRequest));
            }
        } catch (Exception $e) {
            Log::error('Failed to send leave request notification', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
