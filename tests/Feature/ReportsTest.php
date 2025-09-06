<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_type' => 'admin'
        ]);
        
        // Create employee record
        Employee::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP001',
            'department' => 'IT',
            'position' => 'Developer',
            'hire_date' => Carbon::now()->subMonths(6),
            'status' => 'active',
            'salary' => 50000.00,
        ]);
        
        // Create attendance records
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in_time' => '08:00:00',
            'clock_out_time' => '17:00:00',
            'hours_worked' => 8.00,
            'status' => 'present',
        ]);
        
        $this->actingAs($user);
    }

    public function test_reports_index_page_loads()
    {
        $response = $this->get(route('reports.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('stats');
    }

    public function test_attendance_report_page_loads()
    {
        $response = $this->get(route('reports.attendance'));
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.attendance');
        $response->assertViewHas(['attendances', 'stats', 'departments', 'attendanceTrends']);
    }

    public function test_attendance_export_works()
    {
        $response = $this->get(route('reports.attendance.export'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_attendance_filtering_works()
    {
        $response = $this->get(route('reports.attendance', ['status' => 'present']));
        
        $response->assertStatus(200);
        $response->assertViewHas('attendances');
    }

    public function test_departmental_stats_calculated_correctly()
    {
        $response = $this->get(route('reports.attendance'));
        
        $response->assertStatus(200);
        $departmentalStats = $response->viewData('departmentalStats');
        
        $this->assertNotEmpty($departmentalStats);
        $this->assertArrayHasKey('department', $departmentalStats[0]);
        $this->assertArrayHasKey('attendance_rate', $departmentalStats[0]);
    }
}
