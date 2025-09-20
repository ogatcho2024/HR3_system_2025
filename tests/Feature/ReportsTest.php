<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('reports index page loads', function () {
    // Create test data
    $user = User::factory()->create(['account_type' => 'admin']);
    
    Employee::create([
        'user_id' => $user->id,
        'employee_id' => 'EMP001',
        'department' => 'IT',
        'position' => 'Developer',
        'hire_date' => Carbon::now()->subMonths(6),
        'status' => 'active',
        'salary' => 50000.00,
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get(route('reports.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('reports.index');
    $response->assertViewHas('stats');
});

test('attendance report page loads', function () {
    $user = User::factory()->create(['account_type' => 'admin']);
    
    Employee::create([
        'user_id' => $user->id,
        'employee_id' => 'EMP001',
        'department' => 'IT',
        'position' => 'Developer',
        'hire_date' => Carbon::now()->subMonths(6),
        'status' => 'active',
        'salary' => 50000.00,
    ]);
    
    Attendance::create([
        'user_id' => $user->id,
        'date' => Carbon::now()->toDateString(),
        'clock_in_time' => '08:00:00',
        'clock_out_time' => '17:00:00',
        'hours_worked' => 8.00,
        'status' => 'present',
    ]);
    
    $this->actingAs($user);
    
    $response = $this->get(route('reports.attendance'));
    
    $response->assertStatus(200);
    $response->assertViewIs('reports.attendance');
    $response->assertViewHas(['attendances', 'stats', 'departments', 'attendanceTrends']);
});
