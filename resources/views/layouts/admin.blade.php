<!DOCTYPE html>
<html lang="id" data-admin-theme="rose" data-theme-storage-key="sn-store-admin-theme-{{ auth()->id() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#be123c">
    <title>@yield('title', 'Dashboard') · Admin SN Store</title>
    @include('partials.admin.theme-bootstrap')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f8fa] font-sans text-slate-800 antialiased" x-data="adminShell" @keydown.escape.window="closeSidebar()">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:p-4">Lewati ke konten</a>
    <div x-cloak x-show="sidebarOpen && !desktop" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm" aria-hidden="true" @click="closeSidebar()"></div>
    @include('partials.admin.sidebar')
    <div class="flex min-h-screen min-w-0 flex-col lg:pl-64" :inert="sidebarOpen && !desktop">
        @include('partials.admin.header')
        <main id="admin-content" class="mx-auto w-full max-w-[1600px] flex-1 px-4 py-6 sm:px-7 lg:px-8 lg:py-8" tabindex="-1">
            @yield('content')
        </main>
        <footer class="mx-auto flex w-full max-w-[1600px] flex-wrap justify-between gap-2 px-4 pb-6 text-[11px] text-slate-400 sm:px-7 lg:px-8"><p>© {{ date('Y') }} SN Store. Semua hak dilindungi.</p><p>Ruang kelola untuk langkah sehat yang lebih baik.</p></footer>
    </div>
</body>
</html>
