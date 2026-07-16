<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-base-100 shadow-lg transform transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:relative md:flex md:flex-col md:min-h-screen border-r border-base-300">
    <!-- Close button (mobile) -->
    <div class="flex justify-end p-2 md:hidden">
        <button onclick="toggleSidebar()" class="btn btn-ghost btn-sm">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 py-4 mb-2">
        <img src="{{ asset('images/logo-icon.svg') }}" alt="Logo" class="h-8 w-8" onerror="this.style.display='none'">
        <span
            class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Clusterfiy</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3">
        <ul class="menu menu-sm lg:menu-md p-0 gap-0.5">
            <li>
                @php
                    $dashboardRoute =
                        isset($currentCompany) && $currentCompany && $currentCompany->subdomain
                            ? route('tenant.dashboard', ['subdomain' => $currentCompany->subdomain])
                            : route('dashboard');
                @endphp
                <a href="{{ $dashboardRoute }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard') ? 'bg-primary/10 text-primary' : '' }}">
                    <i class="fas fa-home w-5 text-lg"></i> Dashboard
                </a>
            </li>

            @if (isset($currentCompany) && $currentCompany)
                @php
                    $user = auth()->user();
                    $isAdmin = $user->isSuperAdmin() || $user->hasRole('company_admin');
                    $isManager = $user->hasRole('manager');
                @endphp

                <li>
                    <a href="{{ route('tasks.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('tasks.*') ? 'bg-primary/10 text-primary' : '' }}">
                        <i class="fas fa-tasks w-5 text-lg"></i> Tasks
                        @php
                            $taskCount = \App\Models\Task::where('company_id', session('current_company_id'))
                                ->where('status', '!=', 'done')
                                ->count();
                        @endphp
                        @if ($taskCount > 0)
                            <span class="badge badge-primary badge-sm ml-auto">{{ $taskCount }}</span>
                        @endif
                    </a>
                </li>

                @if ($isAdmin || $isManager)
                    <li>
                        <a href="{{ route('users.index') }}"
                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('users.*') ? 'bg-primary/10 text-primary' : '' }}">
                            <i class="fas fa-users w-5 text-lg"></i> Users
                        </a>
                    </li>
                @endif

                @if ($isAdmin || $isManager)
                    <li>
                        <a href="{{ route('reports.index') }}"
                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary/10 text-primary' : '' }}">
                            <i class="fas fa-chart-line w-5 text-lg"></i> Reports
                        </a>
                    </li>
                @endif

                @if ($isAdmin)
                    <li>
                        <a href="{{ route('departments.index') }}"
                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('departments.*') ? 'bg-primary/10 text-primary' : '' }}">
                            <i class="fas fa-building w-5 text-lg"></i> Departments
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('profile.*') ? 'bg-primary/10 text-primary' : '' }}">
                        <i class="fas fa-user-cog w-5 text-lg"></i> Profile
                    </a>
                </li>
            @else
                <li class="opacity-50 text-sm py-2 px-4">
                    <i class="fas fa-info-circle mr-2"></i> Select a company to manage
                </li>
            @endif

            @if (auth()->user()->isSuperAdmin())
                <li class="menu-title mt-4 text-xs uppercase opacity-50 px-4">Admin</li>
                <li>
                    <a href="{{ route('companies.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-base-200 transition-colors {{ request()->routeIs('companies.*') ? 'bg-primary/10 text-primary' : '' }}">
                        <i class="fas fa-globe w-5 text-lg"></i> All Companies
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Company Switcher (Super Admin) -->
    @if (auth()->user()->isSuperAdmin())
        <div class="border-t border-base-300 p-4">
            <form method="POST" action="{{ route('switch-company') }}" class="flex flex-col gap-2">
                @csrf
                <select name="company_id" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                    <option value="">🌍 Global View</option>
                    @foreach (\App\Models\Company::all() as $comp)
                        <option value="{{ $comp->id }}"
                            {{ session('current_company_id') == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @if (session('current_company_id'))
                <form method="POST" action="{{ route('reset-company') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-xs w-full text-xs">Reset to Global</button>
                </form>
            @endif
        </div>
    @endif

    <!-- User Menu (Bottom) -->
    <div class="border-t border-base-300 p-4">
        <div class="dropdown dropdown-top w-full">
            <div tabindex="0"
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer transition">
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
                <li><a href="#" onclick="document.getElementById('logout-form').submit();"><i
                            class="fas fa-sign-out-alt w-4"></i> Logout</a></li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </ul>
        </div>
    </div>
</aside>

<!-- Overlay (mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }
        }
    });
</script>
