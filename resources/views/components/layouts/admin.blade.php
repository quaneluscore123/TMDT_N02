<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} - {{ config('app.name') }}</title>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen">

        {{-- Sidebar Overlay (mobile) --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 md:hidden">
        </div>

        {{-- Sidebar --}}
        <aside class="w-64 bg-indigo-700 text-white min-h-screen fixed z-50
                       -translate-x-full transition-transform duration-300
                       md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-4 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold">
                    SocialShop Admin
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.products.*') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Sản phẩm
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Đơn hàng
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Người dùng
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Danh mục
                </a>

                <a href="{{ route('admin.coupons.index') }}"
                   class="flex items-center px-4 py-3 {{ request()->routeIs('admin.coupons.*') ? 'bg-indigo-800' : 'hover:bg-indigo-600' }}">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Mã giảm giá
                </a>

                <div class="border-t border-indigo-600 mt-4 pt-4">
                    <a href="{{ route('home') }}"
                       class="flex items-center px-4 py-3 hover:bg-indigo-600">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Về trang chủ
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-3 hover:bg-indigo-600 text-left">
                            <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 md:ml-64">
            {{-- Top Bar --}}
            <header class="bg-white shadow-sm h-16 flex items-center px-6 sticky top-0 z-30">
                {{-- Hamburger (mobile) --}}
                <button @click="sidebarOpen = true" class="md:hidden mr-4 text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">{{ $header ?? 'Dashboard' }}</h1>
                <div class="ml-auto flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Xin chào, {{ Auth::user()->name }}</span>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="p-4 md:p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
