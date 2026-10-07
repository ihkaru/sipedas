<?php

namespace Tests\Feature;

use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\AuditLogResource;
use App\Models\ApiAuditLog;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'operator_umum', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@bps.go.id',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->regularUser = User::factory()->create([
            'email' => 'staff@bps.go.id',
        ]);
    }

    public function test_guest_is_redirected_to_filament_login(): void
    {
        $response = $this->get('/a/api-keys');
        $response->assertStatus(302);
        $response->assertRedirect('/a/login');

        $responseAudit = $this->get('/a/audit-logs');
        $responseAudit->assertStatus(302);
        $responseAudit->assertRedirect('/a/login');
    }

    public function test_super_admin_can_access_api_keys_and_audit_logs(): void
    {
        $responseKeys = $this->actingAs($this->superAdmin)->get('/a/api-keys');
        $responseKeys->assertStatus(200);

        $responseAudit = $this->actingAs($this->superAdmin)->get('/a/audit-logs');
        $responseAudit->assertStatus(200);
    }

    public function test_regular_user_cannot_access_audit_logs(): void
    {
        $responseAudit = $this->actingAs($this->regularUser)->get('/a/audit-logs');
        $responseAudit->assertStatus(403);
    }

    public function test_api_key_resource_configuration(): void
    {
        $this->assertEquals('API Key', ApiKeyResource::getModelLabel());
        $this->assertEquals('API Keys', ApiKeyResource::getPluralModelLabel());
        $this->assertEquals('Integrasi & API', ApiKeyResource::getNavigationGroup());

        $this->actingAs($this->superAdmin);
        $this->assertTrue(ApiKeyResource::canViewAny());
    }

    public function test_audit_log_resource_configuration(): void
    {
        $this->assertEquals('Audit Log API', AuditLogResource::getModelLabel());
        $this->assertEquals('Integrasi & API', AuditLogResource::getNavigationGroup());

        $this->assertFalse(AuditLogResource::canCreate());
    }
}
