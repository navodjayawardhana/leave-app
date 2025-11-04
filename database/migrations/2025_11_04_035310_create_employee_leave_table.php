<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_leave', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('leave_date');
            $table->string('leave_type');
            $table->string('leave_reason');
            $table->string('leave_status');
            $table->string('leave_attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave');
    }
};
