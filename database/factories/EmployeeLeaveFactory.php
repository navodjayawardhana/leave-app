<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeLeave>
 */
class EmployeeLeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => User::factory(),
            'leave_date' => $this->faker->date(),
            'leave_type' => $this->faker->randomElement(['Sick Leave', 'Casual Leave', 'Annual Leave']),
            'leave_reason' => $this->faker->sentence(8),
            'leave_status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            'leave_attachment' => null,
        ];
    }
}
