<div class="w-64 bg-base-100 min-h-screen p-4 shadow-lg hidden md:block border-r border-base-300">
    <!-- Logo -->
    <div class="flex items-center gap-2 mb-8 px-2">
        <img src="{{ asset('images/Clusterfiy-logo.png') }}" alt="Logo" class="h-8 w-8">
        <span class="text-xl font-bold">Clusterfiy</span>
    </div>

    <!-- Navigation -->
    <ul class="menu menu-sm lg:menu-md p-0 gap-1">
        <li>
            @php
                // Determine the correct dashboard link
                $dashboardRoute = isset($currentCompany) && $currentCompany && $currentCompany->subdomain
                    ? route('tenant.dashboard', ['subdomain' => $currentCompany->subdomain])
                    : route('dashboard');
            @endphp
            <a href="{{ $dashboardRoute }}" class="flex items-center gap-3">
                <i class="fas fa-home w-5"></i> Dashboard
            </a>
        </li>

        {{-- Only show tenant-specific links if a company is selected --}}
        @if(isset($currentCompany) && $currentCompany)
            <li>
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-3">
                    <i class="fas fa-tasks w-5"></i> Tasks
                </a>
            </li>
            <li>
                <a href="{{ route('departments.index') }}" class="flex items-center gap-3">
                    <i class="fas fa-building w-5"></i> Departments
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}" class="flex items-center gap-3">
                    <i class="fas fa-users w-5"></i> Users
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3">
                    <i class="fas fa-chart-line w-5"></i> Reports
                </a>
            </li>
        @else
            {{-- Show a placeholder when no company is selected --}}
            <li class="opacity-50 text-sm py-2 px-4">
                <i class="fas fa-info-circle mr-2"></i> Select a company to manage
            </li>
        @endif

        @if(auth()->user()->isSuperAdmin())
        <li class="menu-title mt-4 text-xs uppercase opacity-50">Admin</li>
        <li>
            <a href="{{ route('companies.index') }}" class="flex items-center gap-3">
                <i class="fas fa-globe w-5"></i> All Companies
            </a>
        </li>
        @endif
    </ul>

    <!-- Company Switcher (Super Admin) -->
    @if(auth()->user()->isSuperAdmin())
    <div class="mt-6 pt-4 border-t border-base-300">
        <form method="POST" action="{{ route('switch-company') }}" class="flex flex-col gap-2">
            @csrf
            <select name="company_id" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                <option value="">🌍 Global View</option>
                @foreach(\App\Models\Company::all() as $comp)
                <option value="{{ $comp->id }}" {{ session('current_company_id') == $comp->id ? 'selected' : '' }}>
                    {{ $comp->name }}
                </option>
                @endforeach
            </select>
        </form>
        @if(session('current_company_id'))
        <form method="POST" action="{{ route('reset-company') }}" class="mt-1">
            @csrf
            <button type="submit" class="btn btn-ghost btn-xs w-full">Reset to Global</button>
        </form>
        @endif
    </div>
    @endif

    <!-- User Menu (Bottom) -->
    <div class="mt-auto pt-4 border-t border-base-300">
        <div class="dropdown dropdown-top w-full">
            <div tabindex="0" class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer transition">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-10">
                        <span class="text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs opacity-50 truncate">{{ auth()->user()->email }}</p>
                </div>
                <i class="fas fa-chevron-down text-xs opacity-50"></i>
            </div>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-full">
                <li><a href="{{ route('profile.edit') }}"><i class="fas fa-user w-4"></i> Profile</a></li>
                <li><a href="#" onclick="document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt w-4"></i> Logout</a></li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </ul>
        </div>
    </div>
</div>
