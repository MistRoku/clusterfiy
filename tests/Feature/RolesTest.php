<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'company_admin', 'manager', 'employee'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
    }

    public function test_role_matrix(): void
    {
        $company = Company::create(['name' => 'Acme', 'subdomain' => 'acme' . rand(100, 999), 'is_active' => true, 'created_by' => User::factory()->create()->id]);

        $owner = User::factory()->create(['company_id' => $company->id]);
        $owner->assignRole('company_admin');
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');
        $member = User::factory()->create(['company_id' => $company->id]);
        $member->assignRole('employee');

        $task = Task::withoutGlobalScopes()->create(['company_id' => $company->id, 'title' => 'T', 'status' => 'todo', 'priority' => 'low', 'created_by' => $owner->id]);

        // Owner manages members/billing + delete tasks.
        $this->assertTrue($owner->can('delete', $task));
        $this->assertTrue($owner->can('manageBilling', $company));
        $this->assertTrue($owner->can('manageMembers', $company));

        // Manager manages tasks but not billing/company delete.
        $this->assertTrue($manager->can('update', $task));
        $this->assertTrue($manager->can('approve', $task));
        $this->assertFalse($manager->can('manageBilling', $company));
        $this->assertFalse($manager->can('delete', $company));

        // Member cannot reach admin routes: cannot approve, cannot manage members.
        $this->assertFalse($member->can('approve', $task));
        $this->assertFalse($member->can('manageMembers', $company));
        $this->assertFalse($member->can('manageBilling', $company));
    }

    public function test_super_admin_is_separate_from_company_roles(): void
    {
        $super = User::factory()->create(['is_super_admin' => true]);
        $super->assignRole('super_admin');
        $company = Company::withoutGlobalScopes()->create(['name' => 'Acme', 'subdomain' => 'acme' . rand(100, 999), 'is_active' => true, 'created_by' => $super->id]);
        $this->assertTrue($super->can('viewAny', Company::class));
        $member = User::factory()->create(['company_id' => $company->id]);
        $member->assignRole('employee');
        $this->assertFalse($member->can('viewAny', Company::class));
    }
}
