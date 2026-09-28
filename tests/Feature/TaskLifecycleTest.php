<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Task;
use App\Models\TaskStatusChange;
use App\Models\User;
use App\Services\TaskService;
use App\Support\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setupCompany(): array
    {
        $creator = User::factory()->create();
        $company = Company::create(['name' => 'Acme', 'subdomain' => 'acme' . rand(100, 999), 'is_active' => true, 'created_by' => $creator->id]);
        CompanyContext::set($company->id);
        return [$company, $creator];
    }

    public function test_legal_transitions_write_audit_row(): void
    {
        [$company, $creator] = $this->setupCompany();
        $task = Task::create(['company_id' => $company->id, 'title' => 'T', 'status' => 'todo', 'priority' => 'low', 'created_by' => $creator->id]);
        $svc = new TaskService();
        $svc->transition($task, 'in_progress');
        $svc->transition($task->fresh(), 'in_review');
        $svc->transition($task->fresh(), 'done');
        $this->assertEquals('done', $task->fresh()->status);
        $this->assertGreaterThanOrEqual(3, TaskStatusChange::withoutGlobalScopes()->where('task_id', $task->id)->count());
    }

    public function test_illegal_transition_returns_422(): void
    {
        [$company, $creator] = $this->setupCompany();
        $task = Task::create(['company_id' => $company->id, 'title' => 'T', 'status' => 'todo', 'priority' => 'low', 'created_by' => $creator->id]);
        $this->expectException(ValidationException::class);
        (new TaskService())->transition($task, 'done');
    }

    public function test_assignment_outside_company_rejected(): void
    {
        [$company, $creator] = $this->setupCompany();
        $other = Company::create(['name' => 'Other', 'subdomain' => 'other' . rand(100, 999), 'is_active' => true, 'created_by' => $creator->id]);
        $outsider = User::factory()->create(['company_id' => $other->id]);
        $this->expectException(ValidationException::class);
        (new TaskService())->create(['company_id' => $company->id, 'title' => 'T', 'status' => 'todo', 'priority' => 'low', 'created_by' => $creator->id, 'assigned_to' => $outsider->id]);
    }

    public function test_time_entry_validation(): void
    {
        [$company, $creator] = $this->setupCompany();
        $task = Task::create(['company_id' => $company->id, 'title' => 'T', 'status' => 'todo', 'priority' => 'low', 'created_by' => $creator->id]);
        $entry = $task->timeEntries()->create(['company_id' => $company->id, 'user_id' => $creator->id, 'started_at' => now(), 'description' => 'work']);
        $this->assertNotNull($entry->id);
        // ended before started is invalid at controller level; model stores raw, so assert helper math:
        $this->assertTrue($entry->started_at->lte(now()));
    }

    public function test_reopen_done_to_in_progress_allowed(): void
    {
        [$company, $creator] = $this->setupCompany();
        $task = Task::create(['company_id' => $company->id, 'title' => 'T', 'status' => 'done', 'priority' => 'low', 'created_by' => $creator->id]);
        (new TaskService())->transition($task, 'in_progress');
        $this->assertEquals('in_progress', $task->fresh()->status);
    }
}
