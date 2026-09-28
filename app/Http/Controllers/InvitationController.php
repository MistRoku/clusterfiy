<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use App\Notifications\MemberAddedToCompany;
use App\Notifications\SendCompanyInvite;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        $companyId = CompanyContext::get();
        $invitations = CompanyInvitation::where('company_id', $companyId)->latest()->paginate(15);
        return view('invitations.index', compact('invitations'));
    }

    public function store(StoreInvitationRequest $request)
    {
        $companyId = CompanyContext::get();
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);

        // Reject invite to an existing member.
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->belongsToCompany($company->id)) {
            return back()->withErrors(['email' => 'This user is already a member of the company.'])->withInput();
        }

        // Enforce plan member limits.
        $limit = config("plans.{$company->plan}.max_members_per_company");
        if ($limit !== null) {
            $count = $company->memberships()->count() + User::where('company_id', $company->id)->count();
            if ($count >= $limit) {
                return back()->withErrors(['email' => 'Member limit for the Free plan reached (5). Upgrade to Team.'])->withInput();
            }
        }

        // No duplicate pending invitation.
        $dup = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();
        if ($dup) {
            return back()->withErrors(['email' => 'A pending invitation already exists for this email.'])->withInput();
        }

        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(40),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addHours(72),
        ]);

        if ($existingUser) {
            $existingUser->notify(new SendCompanyInvite($invitation));
        } else {
            Notification::route('mail', $invitation->email)->notify(new SendCompanyInvite($invitation));
        }

        return back()->with('success', 'Invitation sent to ' . $invitation->email);
    }

    /** Public accept endpoint: /invitations/accept/{token} */
    public function accept(string $token)
    {
        $invitation = CompanyInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isUsed()) {
            return redirect()->route('login')->withErrors(['token' => 'This invitation has already been used.']);
        }
        if ($invitation->isExpired()) {
            return redirect()->route('login')->withErrors(['token' => 'This invitation has expired. Ask for a new one.']);
        }

        if (! Auth::check()) {
            session(['pending_invitation_token' => $token]);
            return redirect()->route('register', ['email' => $invitation->email])
                ->with('status', 'Register with ' . $invitation->email . ' then your invitation will be accepted.');
        }

        $user = Auth::user();
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return back()->withErrors(['token' => 'This invitation was sent to a different email address.']);
        }

        $this->acceptFor($invitation, $user);

        return redirect()->route('dashboard')->with('success', 'Welcome to ' . $invitation->company->name);
    }

    public function destroy(CompanyInvitation $invitation)
    {
        $this->authorize('delete', $invitation);
        $invitation->delete();
        return back()->with('success', 'Invitation cancelled.');
    }

    protected function acceptFor(CompanyInvitation $invitation, User $user): void
    {
        $map = ['owner' => 'company_admin', 'manager' => 'manager', 'member' => 'employee'];

        \DB::transaction(function () use ($invitation, $user, $map) {
            // Company membership uniqueness: skip if already a member.
            $exists = \DB::table('company_user')
                ->where('company_id', $invitation->company_id)
                ->where('user_id', $user->id)
                ->exists();
            if (! $exists) {
                \DB::table('company_user')->insert([
                    'company_id' => $invitation->company_id,
                    'user_id' => $user->id,
                    'role' => $invitation->role,
                    'invited_by' => $invitation->invited_by,
                    'accepted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if (! $user->company_id) {
                $user->company_id = $invitation->company_id;
                $user->save();
            }
            $user->assignRole($map[$invitation->role] ?? 'employee');

            $invitation->accepted_at = now();
            $invitation->save();

            session(['current_company_id' => $invitation->company_id]);
            CompanyContext::set($invitation->company_id);

            $user->notify(new MemberAddedToCompany($invitation->company, $invitation->role));
        });
    }
}
