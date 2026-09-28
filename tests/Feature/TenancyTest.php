<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'subdomain' => \Str::slug($name) . rand(100, 999),
            'is_active' => true,
            'created_by' => User::factory()->create()->id,
        ]);
    }

    public function test_lists_contain_only_current_company_rows(): void
    {
        $a = $this->makeCompany('Alpha Co');
        $b = $this->makeCompany('Beta Co');
        $user = User::factory()->create(['company_id' => $a->id]);

        CompanyContext::set($a->id);
        Task::create(['company_id' => $a->id, 'title' => 'A task', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);
        CompanyContext::set($b->id);
        Task::create(['company_id' => $b->id, 'title' => 'B task', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);

        CompanyContext::set($a->id);
        $this->assertEquals(1, Task::count());
        $this->assertEquals('A task', Task::first()->title);
    }

    public function test_cross_company_task_access_is_blocked_by_scope(): void
    {
        $a = $this->makeCompany('Alpha Co');
        $b = $this->makeCompany('Beta Co');
        $user = User::factory()->create(['company_id' => $a->id]);
        $t = Task::withoutGlobalScopes()->create(['company_id' => $b->id, 'title' => 'B secret', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);

        CompanyContext::set($a->id);
        $this->assertNull(Task::find($t->id));
        $this->assertFalse($user->can('view', $t));
    }

    public function test_company_switch_changes_visible_data(): void
    {
        $a = $this->makeCompany('Alpha Co');
        $b = $this->makeCompany('Beta Co');
        $user = User::factory()->create(['company_id' => $a->id]);
        Task::withoutGlobalScopes()->create(['company_id' => $a->id, 'title' => 'A', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);
        Task::withoutGlobalScopes()->create(['company_id' => $b->id, 'title' => 'B', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);

        CompanyContext::set($a->id);
        $this->assertEquals('A', Task::first()->title);
        CompanyContext::set($b->id);
        $this->assertEquals('B', Task::first()->title);
    }

    public function test_scope_cannot_be_bypassed_via_query_string(): void
    {
        $a = $this->makeCompany('Alpha Co');
        $b = $this->makeCompany('Beta Co');
        $user = User::factory()->create(['company_id' => $a->id]);
        Task::withoutGlobalScopes()->create(['company_id' => $b->id, 'title' => 'B', 'status' => 'todo', 'priority' => 'low', 'created_by' => $user->id]);

        CompanyContext::set($a->id);
        // Even with explicit company_id filter for another tenant, scope still applies (AND condition).
        $count = Task::where('company_id', $b->id)->count();
        $this->assertEquals(0, $count);
    }
}
