<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <img src="{{ asset('images/Clusterfiy-Logo.png') }}" alt="Clusterfiy" class="h-8 w-auto block md:hidden" />
                    <img src="{{ asset('images/Clusterfiy-Full.png') }}" alt="Clusterfiy"
                        class="h-8 w-auto hidden md:block" />
                </a>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => {
                    localStorage.setItem('darkMode', val);
                    document.documentElement.classList.toggle('dark', val);
                })"
                    x-effect="document.documentElement.classList.toggle('dark', darkMode)">
                    <button @click="darkMode = !darkMode"
                        class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <svg x-show="!darkMode" class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                </div>

                @auth
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <span class="hidden md:inline text-sm">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-20">
                            <a href="{{ route('dashboard') }}"
                                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                            @if (auth()->user()->isSuperAdmin())
                                <a href="{{ route('companies.index') }}"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">Manage
                                    Companies</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
