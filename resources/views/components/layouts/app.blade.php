<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <title>{{ $metaTitle ?? ($title ?? config('app.name', 'SocialShop')) }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'SocialShop - Thời trang và phong cách sống. Mua sắm online với nhiều ưu đãi hấp dẫn.' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? 'thời trang, fashion, mua sắm online, áo quần, phụ kiện' }}">
    <meta name="author" content="SocialShop">
    <meta name="robots" content="index, follow">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $metaTitle ?? ($title ?? 'SocialShop') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'SocialShop - Thời trang và phong cách sống' }}">
    <meta property="og:image" content="{{ $metaImage ?? asset('images/og-image.jpg') }}">
    <meta property="og:site_name" content="SocialShop">
    
    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? ($title ?? 'SocialShop') }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'SocialShop - Thời trang và phong cách sống' }}">
    <meta name="twitter:image" content="{{ $metaImage ?? asset('images/og-image.jpg') }}">
    
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">
    
    {{-- JSON-LD Structured Data --}}
    @isset($jsonLd)
        <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endisset
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
    <style>
        :root {
            --cream: #faf7f4;
            --blush: #e8c4c4;
            --rose: #c9a9a6;
            --rose-deep: #b8847e;
            --charcoal: #3d3d3d;
            --soft-border: #efe8e3;
        }
        body { background: var(--cream); color: var(--charcoal); font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, h5, .font-serif { font-family: 'Cormorant Garamond', serif; }
        .nav-link { color: var(--charcoal) !important; font-weight: 500; font-size: .92rem; }
        .nav-link:hover { color: var(--rose-deep) !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', { count: {{ auth()->check() ? app(\App\Services\Cart\CartService::class)->getItemCount(auth()->id()) : 0 }} });
        });
    </script>

    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-[#faf7f4]/95 backdrop-blur-md border-b border-[#efe8e3]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="text-2xl font-serif font-bold text-[#3d3d3d] tracking-wider">
                        SocialShop
                    </a>
                </div>

                {{-- Search Bar --}}
                <div class="hidden md:flex flex-1 max-w-lg mx-8">
                    <form action="{{ route('products.index') }}" method="GET" class="w-full">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Tìm sản phẩm..."
                                   class="w-full pl-10 pr-4 py-2 border border-[#efe8e3] rounded-lg bg-white focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all text-sm">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-[#9a9490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>

                {{-- Navigation --}}
                <nav class="flex items-center space-x-1">
                    <a href="{{ route('products.index') }}"
                       class="nav-link px-3 py-2">
                        Sản phẩm
                    </a>

                    {{-- Mobile Search Toggle --}}
                    <div x-data="{ open: false }" class="md:hidden">
                        <button @click="open = !open" class="nav-link px-3 py-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-[#efe8e3] p-3 z-50">
                            <form action="{{ route('products.index') }}" method="GET">
                                <div class="relative">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           placeholder="Tìm sản phẩm..."
                                           class="w-full pl-10 pr-4 py-2.5 border border-[#efe8e3] rounded-lg bg-white focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all text-sm">
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-[#9a9490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </form>
                        </div>
                    </div>

                    @auth
                        {{-- Cart --}}
                        <a href="{{ route('cart.index') }}"
                           class="relative nav-link px-3 py-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <span x-data x-show="$store.cart.count > 0" x-cloak
                                  x-text="$store.cart.count > 99 ? '99+' : $store.cart.count"
                                  class="absolute -top-0.5 -right-1 bg-[#c9a9a6] text-white text-[10px] font-bold rounded-full h-4 min-w-[16px] flex items-center justify-center px-1"></span>
                        </a>

                        {{-- User Menu --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center space-x-1.5 nav-link px-2 py-1.5 rounded-lg hover:bg-[#f5f0ec] transition-all">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="h-7 w-7 rounded-full object-cover border border-[#efe8e3]">
                                @else
                                    <div class="h-7 w-7 rounded-full bg-[#b8847e] flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <svg class="h-3.5 w-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#efe8e3] py-1 z-50">
                                <div class="px-4 py-2.5 border-b border-[#efe8e3]">
                                    <p class="font-semibold text-[#3d3d3d] text-sm">{{ Auth::user()->name }}</p>
                                    <p class="text-[#9a9490] text-xs mt-0.5">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3d3d3d] hover:bg-[#faf7f4] hover:text-[#b8847e] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Tài khoản
                                </a>
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3d3d3d] hover:bg-[#faf7f4] hover:text-[#b8847e] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Đơn hàng
                                </a>
                                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3d3d3d] hover:bg-[#faf7f4] hover:text-[#b8847e] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Yêu thích
                                </a>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3d3d3d] hover:bg-[#faf7f4] hover:text-[#b8847e] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Quản trị
                                    </a>
                                @endif
                                <div class="border-t border-[#efe8e3] mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-[#b8847e] hover:bg-[#faf7f4] transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Đăng xuất
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link px-3 py-2">Đăng nhập</a>
                        <a href="{{ route('register') }}"
                           class="bg-[#b8847e] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors">
                            Đăng ký
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm" role="alert">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer id="contact" class="bg-[#2c2a28] text-[#c9c4be] mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Contact --}}
                <div>
                    <h5 class="font-serif text-[#faf7f4] text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Liên hệ chúng tôi
                    </h5>
                    <p class="text-sm mb-2">📍 123 Đường Thời Trang, Quận 1, TP.HCM</p>
                    <p class="text-sm mb-2">📞 <a href="tel:1900xxxx" class="hover:text-[#e8c4c4] transition-colors">1900-xxxx-xxx</a></p>
                    <p class="text-sm mb-2">✉️ <a href="mailto:support@socialshop.vn" class="hover:text-[#e8c4c4] transition-colors">support@socialshop.vn</a></p>
                    <p class="text-sm">🕐 Thứ 2 - Chủ nhật: 8:00 - 21:00</p>
                </div>

                {{-- Social --}}
                <div>
                    <h5 class="font-serif text-[#faf7f4] text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Kết nối với chúng tôi
                    </h5>
                    <div class="flex gap-2 mb-4">
                        <a href="#" class="w-9 h-9 rounded-full bg-[#3d3a37] flex items-center justify-center hover:bg-[#b8847e] transition-colors" title="Facebook">
                            <svg class="h-4 w-4 fill-current text-[#faf7f4]" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-[#3d3a37] flex items-center justify-center hover:bg-[#b8847e] transition-colors" title="Instagram">
                            <svg class="h-4 w-4 fill-current text-[#faf7f4]" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-[#3d3a37] flex items-center justify-center hover:bg-[#b8847e] transition-colors" title="TikTok">
                            <svg class="h-4 w-4 fill-current text-[#faf7f4]" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48V13a8.28 8.28 0 005.58 2.17v-3.44a4.85 4.85 0 01-2-.92v.92z"/></svg>
                        </a>
                    </div>
                    <p class="text-xs text-[#9a9490]">Theo dõi SocialShop để cập nhật xu hướng thời trang mới nhất và ưu đãi hấp dẫn.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h5 class="font-serif text-[#faf7f4] text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Hỗ trợ
                    </h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('pages.buying-guide') }}" class="hover:text-[#e8c4c4] transition-colors">Hướng dẫn mua hàng</a></li>
                        <li><a href="{{ route('pages.return-policy') }}" class="hover:text-[#e8c4c4] transition-colors">Chính sách đổi trả</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="hover:text-[#e8c4c4] transition-colors">Chính sách bảo mật</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="hover:text-[#e8c4c4] transition-colors">FAQ</a></li>
                    </ul>
                </div>
            </div>

            <hr class="border-[#3d3a37] my-8">

            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-[#9a9490]">&copy; {{ date('Y') }} SocialShop. Hệ thống quản lý bán hàng thời trang.</p>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[#9a9490]">Thanh toán:</span>
                    <span class="bg-[#3d3a37] text-[10px] font-bold px-2 py-0.5 rounded text-[#c9c4be]">COD</span>
                    <span class="bg-[#3d3a37] text-[10px] font-bold px-2 py-0.5 rounded text-[#c9c4be]">VNPAY</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Global Toast Notification --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:toast.window="
            show = true;
            message = $event.detail.message;
            type = $event.detail.type || 'success';
            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(() => show = false, 3000);
         "
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-5 right-5 z-[100] max-w-xs sm:max-w-sm">
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl shadow-2xl"
             :class="type === 'error' ? 'bg-red-600' : 'bg-[#3d3d3d]'">
            <svg x-show="type !== 'error'" class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="type === 'error'" class="w-5 h-5 text-red-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="text-sm font-medium text-white" x-text="message"></span>
        </div>
    </div>

    {{-- Chatbot Widget --}}
    @livewire('chatbot-widget')

    @livewireScripts
    @stack('scripts')
</body>
</html>
