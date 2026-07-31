<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TAYA') }} - Detainee Rights & Overstay Alert System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-gray-50">
        
        <!-- Sidebar Backdrop (Mobile) -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-taya-navy-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 shadow-2xl flex flex-col">
            
            <!-- Logo area -->
            <div class="flex items-center justify-between h-20 px-6 bg-taya-navy-900 border-b border-white/10 shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-taya-accent to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-white">TAYA</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-md hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                @php
                    $currentUser = auth()->user();
                    $isAdmin = $currentUser->isAdmin();
                    $canViewCases = $currentUser->is_active;
                @endphp

                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
                    Dashboard
                </x-nav-link>

                @if($canViewCases)
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/40 uppercase tracking-wider">Detainee Management</div>
                <x-nav-link href="{{ route('detainees.index') }}" :active="request()->routeIs('detainees.*')" icon="users">
                    Detainee Records
                </x-nav-link>
                @endif

                @if($canViewCases)
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/40 uppercase tracking-wider">Alerts & Legal Actions</div>
                <x-nav-link href="{{ route('alerts.index') }}" :active="request()->routeIs('alerts.*')" icon="bell">
                    Alert Queue
                </x-nav-link>
                @endif

                @if($canViewCases)
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/40 uppercase tracking-wider">Reports & Analytics</div>
                <x-nav-link href="{{ route('reports.analytics') }}" :active="request()->routeIs('reports.analytics')" icon="chart-bar">
                    System Analytics
                </x-nav-link>
                @endif

                @if($isAdmin)
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/40 uppercase tracking-wider">Administration</div>
                <x-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="user-group">
                    User Management
                </x-nav-link>
                <x-nav-link href="{{ route('admin.facilities.index') }}" :active="request()->routeIs('admin.facilities.*')" icon="building">
                    Facilities
                </x-nav-link>
                <x-nav-link href="{{ route('admin.penalties.index') }}" :active="request()->routeIs('admin.penalties.*')" icon="scale">
                    Penalty References
                </x-nav-link>
                @endif
                @if($isAdmin || $currentUser->isAuditor())
                    <x-nav-link href="{{ route('admin.audit-logs.index') }}" :active="request()->routeIs('admin.audit-logs.*')" icon="clipboard-document-list">
                        Audit Logs
                    </x-nav-link>
                @endif
            </div>
            
            <!-- User Profile (Bottom of sidebar) -->
            <div class="p-4 bg-taya-navy-800 border-t border-white/10 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-taya-navy-600 flex items-center justify-center font-bold text-white shadow-inner">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-white/60 truncate capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 transition-all duration-300">
            <!-- Top Navbar -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 h-20 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 sticky top-0 shadow-sm">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-taya-accent transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <h1 class="text-xl font-bold text-gray-900 ml-2 lg:ml-0 hidden sm:block animate-fade-in">
                        @if(isset($header))
                            {{ $header }}
                        @else
                            @yield('header', 'Dashboard')
                        @endif
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                        @php
                            $recentAlerts = \App\Models\Alert::visibleTo(auth()->user())->with('detainee')->latest()->limit(5)->get();
                            $unresolvedCount = \App\Models\Alert::visibleTo(auth()->user())->whereNull('resolved_at')->count();
                        @endphp
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-400 hover:text-gray-500 relative transition-colors" aria-label="Notifications">
                                @if($unresolvedCount > 0)
                                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                                @endif
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 py-2 z-50" style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                        <a href="{{ route('alerts.index') }}" class="text-xs text-gray-500 hover:underline">View all</a>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ $unresolvedCount }} unresolved alerts</p>
                                </div>

                                <div class="max-h-64 overflow-y-auto">
                                    @forelse($recentAlerts as $r)
                                        <a href="{{ route('alerts.show', $r) }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                            <div class="flex items-start gap-3">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-sm font-semibold text-gray-700">{{ strtoupper(substr($r->alert_level,0,1)) }}</span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $r->detainee->full_name }} <span class="text-xs text-gray-500">• {{ str_replace('_',' ', $r->alert_level) }}</span></p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $r->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="px-4 py-4 text-sm text-gray-500">No recent alerts.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-taya-accent text-white flex items-center justify-center font-bold text-sm shadow-md">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50"
                             style="display: none;">
                            <div class="px-4 py-4 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                <p class="mt-2 text-xs uppercase tracking-wider text-gray-400">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 animate-fade-in relative z-10">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3 shadow-sm animate-slide-in-right">
                        <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-green-800">Success</h3>
                            <div class="mt-1 text-sm text-green-700">{{ session('success') }}</div>
                        </div>
                        <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none p-1 rounded-md hover:bg-green-100 transition-colors"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 shadow-sm animate-slide-in-right">
                        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800">There were {{$errors->count()}} errors with your submission</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-red-500 hover:text-red-700 focus:outline-none p-1 rounded-md hover:bg-red-100 transition-colors"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 shadow-sm animate-slide-in-right">
                        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-1 text-sm text-red-700">{{ session('error') }}</div>
                        </div>
                        <button @click="show = false" class="text-red-500 hover:text-red-700 focus:outline-none p-1 rounded-md hover:bg-red-100 transition-colors"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>
                @endif

                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>
    
    @yield('scripts')
</body>
</html>
