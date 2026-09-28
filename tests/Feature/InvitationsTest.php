<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_happy_path(): void
    {
        $owner = User::factory()->create();
        $company = Company::create(['name' => 'Acme', 'subdomain' => 'acme' . rand(100, 999), 'is_active' => true, 'created_by' => $owner->id]);
        $owner->company_id = $company->id;
        $owner->save();

        $inv = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => 'new@user.test',
            'role' => 'member',
            'token' => str()->random(40),
            'invited_by' => $owner->id,
            'expires_at' => now()->addHours(72),
        ]);

        $this->assertFalse($inv->isUsed());
        $this->assertFalse($inv->isExpired());
    }

    public function test_used_expired_invites_rejected(): void
    {
        $u = User::factory()->create();
        $company = Company::create(['name' => 'Acme', 'subdomain' => 'acme' . rand(100, 999), 'is_active' => true, 'created_by' => $u->id]);

        $used = CompanyInvitation::create(['company_id' => $company->id, 'email' => 'a@a.test', 'role' => 'member', 'token' => str()->random(40), 'invited_by' => $u->id, 'expires_at' => now()->addHour(), 'accepted_at' => now()]);
        $this->assertTrue($used->isUsed());

        $expired = CompanyInvitation::create(['company_id' => $company->id, 'email' => 'b@b.test', 'role' => 'member', 'token' => str()->random(40), 'invited_by' => $u->id, 'expires_at' => now()->subHour()]);
        $this->assertTrue($expired->isExpired());
    }

    public function test_invitation_roles_validated(): void
    {
        $this->assertContains('owner', CompanyInvitation::validRoles());
        $this->assertContains('manager', CompanyInvitation::validRoles());
        $this->assertContains('member', CompanyInvitation::validRoles());
        $this->assertNotContains('super_admin', CompanyInvitation::validRoles());
    }
}
