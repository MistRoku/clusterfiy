<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_notification_can_be_stored(): void
    {
        $creator = User::factory()->create();
        $company = Company::create([
            'name' => 'Acme ' . uniqid(),
            'subdomain' => 'acme' . rand(100000, 999999),
            'is_active' => true,
            'created_by' => $creator->id,
        ]);
        $assignee = User::factory()->create(['company_id' => $company->id]);
        $task = Task::withoutGlobalScopes()->create([
            'company_id' => $company->id, 'title' => 'Notify me', 'status' => 'todo',
            'priority' => 'low', 'created_by' => $creator->id, 'assigned_to' => $assignee->id,
        ]);

        $assignee->notify(new TaskAssigned($task));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $assignee->id,
        ]);
        $this->assertEquals(1, $assignee->fresh()->unreadNotifications()->count());
    }

    public function test_password_reset_tokens_expire_after_30_minutes(): void
    {
        $this->assertEquals(30, config('auth.passwords.users.expire'));
    }

    public function test_self_registration_is_open_by_default_and_honours_the_flag(): void
    {
        $this->assertTrue(config('auth.registration'));

        config()->set('auth.registration', false);
        $this->get('/register')->assertForbidden();
        $this->post('/register', [])->assertForbidden();
    }
}
