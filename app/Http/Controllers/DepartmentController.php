<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index()
    {
        $departments = Department::paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $users = User::where('company_id', session('current_company_id'))->get();
        return view('departments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);
        // manager must belong to current company
        if (! empty($validated['manager_id'])) {
            $mgr = User::whereKey($validated['manager_id'])->first();
            if (! $mgr || ! $mgr->belongsToCompany((int) session('current_company_id'))) {
                return back()->withErrors(['manager_id' => 'Manager must be a member of the current company.']);
            }
        }
        Department::create(array_merge($validated, [
            'is_active' => true,
        ]));
        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        $users = User::where('company_id', session('current_company_id'))->get();
        return view('departments.edit', compact('department', 'users'));
    }

    public function update(Request $request, Department $department)
    {
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
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
