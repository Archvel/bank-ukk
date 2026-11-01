@props(['fullscreen' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Bank Mini Sekolah') }} - Portal Login Terpusat</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased h-full bg-slate-50 selection:bg-emerald-500 selection:text-white">
        @if ($fullscreen)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-100">
                <div class="mb-4">
                    <a href="{{ route('login') }}" class="flex items-center gap-2 font-bold text-xl text-slate-800">
                        <span class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-600/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 3l9 7H3l9-7z" />
                            </svg>
                        </span>
                        <span>Bank Mini Sekolah</span>
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-2 px-6 py-5 bg-white border border-slate-200 shadow-sm overflow-hidden sm:rounded-xl">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
