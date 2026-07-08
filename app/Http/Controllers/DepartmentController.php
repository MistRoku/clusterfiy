<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $companyId = session('current_company_id');
        $departments = Department::where('company_id', $companyId)->paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $companyId = session('current_company_id');
        $users = User::where('company_id', $companyId)->get();
        return view('departments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);
        $companyId = session('current_company_id');
        Department::create(array_merge($validated, ['company_id' => $companyId, 'is_active' => true]));
        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        $companyId = session('current_company_id');
        $this->authorize('update', $department);
        $users = User::where('company_id', $companyId)->get();
        return view('departments.edit', compact('department', 'users'));
    }

    public function update(Request $request, Department $department)
    {
        $this->authorize('update', $department);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);
        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
