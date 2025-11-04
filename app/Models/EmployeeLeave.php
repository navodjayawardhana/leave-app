<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'employee_leave';
    protected $fillable = [
        'employee_id',
        'leave_date',
        'leave_type',
        'leave_reason',
        'leave_status',
        'leave_attachment',
    ];
}
