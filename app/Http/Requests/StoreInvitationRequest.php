<?php

namespace App\Http\Requests;

use App\Models\CompanyInvitation;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CompanyInvitation::class);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:owner,manager,member'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Managers cannot invite owners.
            $actorRole = $this->user()->companyRole((int) session('current_company_id'));
            if (! $this->user()->isSuperAdmin()
                && ! $this->user()->hasRole('company_admin')
                && $actorRole === 'manager'
                && $this->input('role') === 'owner') {
                $validator->errors()->add('role', 'Managers cannot invite owners.');
            }
        });
    }
}
