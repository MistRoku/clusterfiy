<nav class="bg-base-100 shadow-sm border-b border-base-300 px-4 py-3 sticky top-0 z-30">
    <div class="flex justify-between items-center">
        <!-- Left -->
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="btn btn-ghost btn-sm md:hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <span class="text-sm opacity-50 hidden sm:inline">
                {{ $currentCompany->name ?? 'Global View' }}
            </span>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-3">
            @if(isset($currentCompany) && $currentCompany)
            <div class="badge badge-primary badge-sm hidden sm:flex gap-1">
                <i class="fas fa-building text-xs"></i>
                {{ $currentCompany->name }}
            </div>
            @endif

            <!-- Notifications -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" class="btn btn-ghost btn-sm btn-circle relative">
                    <i class="fas fa-bell text-lg"></i>
                    @php
                        $unreadCount = auth()->user()->unreadNotifications->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="badge badge-error badge-xs absolute -top-1 -right-1">{{ $unreadCount }}</span>
                    @endif
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-80 max-h-96 overflow-y-auto">
                    <li class="menu-title flex justify-between">
                        <span>Notifications</span>
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-xs text-primary hover:underline">Mark all read</button>
                            </form>
                        @endif
                    </li>
                    @forelse(auth()->user()->unreadNotifications->take(10) as $notification)
                        <li class="border-b border-base-200 last:border-0">
                            <a href="{{ $notification->data['url'] ?? '#' }}" class="flex items-start gap-3 py-2">
                                <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} text-primary mt-0.5"></i>
                                <div>
                                    <p class="text-sm">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    <p class="text-xs opacity-50">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="text-center opacity-50 py-4 text-sm">No notifications</li>
                    @endforelse
                </ul>
            </div>

            <!-- Dark Mode Toggle -->
            <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
                 x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); })"
                 x-effect="document.documentElement.classList.toggle('dark', darkMode)">
                <label class="swap swap-rotate">
                    <input type="checkbox" x-model="darkMode">
                    <i class="fas fa-sun swap-on text-xl text-warning"></i>
                    <i class="fas fa-moon swap-off text-xl text-primary"></i>
                </label>
            </div>
        </div>
    </div>
</nav>