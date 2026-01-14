<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected $auditLogService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditLogService = new AuditLogService();
    }

    public function test_audit_log_can_be_created_with_valid_action_type(): void
    {
        $log = AuditLog::create([
            'action_type' => 'login',
            'description' => 'Test login',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'login',
            'description' => 'Test login',
        ]);
    }

    public function test_audit_log_validates_action_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AuditLog::create([
            'action_type' => 'invalid_action',
            'description' => 'Test invalid action',
        ]);
    }

    public function test_all_action_types_are_valid(): void
    {
        $actionTypes = AuditLog::ACTION_TYPES;

        foreach ($actionTypes as $type) {
            $log = AuditLog::create([
                'action_type' => $type,
                'description' => "Test {$type}",
            ]);

            $this->assertEquals($type, $log->action_type);
        }

        $this->assertCount(count($actionTypes), AuditLog::all());
    }

    public function test_audit_log_service_can_log_account_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $accountData = [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'account_type' => 'Employee',
        ];

        $log = $this->auditLogService->logAccountCreated(999, $accountData);

        $this->assertEquals('account_created', $log->action_type);
        $this->assertStringContainsString('John Doe', $log->description);
        $this->assertEquals($accountData, $log->new_values);
    }

    public function test_audit_log_scopes_work_correctly(): void
    {
        // Create various log types
        AuditLog::create(['action_type' => 'login', 'description' => 'Test']);
        AuditLog::create(['action_type' => 'failed_login', 'description' => 'Test']);
        AuditLog::create(['action_type' => 'create', 'description' => 'Test']);
        AuditLog::create(['action_type' => 'account_created', 'description' => 'Test']);

        $this->assertEquals(2, AuditLog::authActions()->count());
        $this->assertEquals(1, AuditLog::dataActions()->count());
        $this->assertEquals(1, AuditLog::accountActions()->count());
        $this->assertEquals(1, AuditLog::failedLoginAttempts()->count());
    }
}
