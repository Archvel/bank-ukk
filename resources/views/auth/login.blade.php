<x-guest-layout :fullscreen="true">
    <div class="min-h-screen flex items-center justify-center bg-slate-950 relative overflow-hidden px-4 sm:px-6">
        <!-- Radial Glow Accents (Subtle Modern Blur) -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Main Card -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 z-10">
            <!-- Top Card Header: Centered Logo & Title -->
            <div class="text-center mb-8">
                <div class="bg-emerald-600 text-white p-3 rounded-xl inline-flex shadow-lg shadow-emerald-600/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 3l9 7H3l9-7z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mt-4 tracking-tight">
                    E-Teller Bank Mini
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Masuk ke ruang kerja Anda
                </p>
            </div>

            <!-- Session Status Alert (if any) -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Clean Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Input 1: Email atau Username -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email atau Username
                    </label>
                    <input
                        id="email"
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Masukkan email atau username"
                        class="w-full rounded-xl border-gray-200 focus:border-emerald-600 focus:ring-emerald-600 py-3 px-4 text-sm text-gray-900 placeholder:text-gray-400 transition shadow-sm @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Input 2: Kata Sandi with Show/Hide Toggle -->
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="show ? 'text' : 'password'"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••••••"
                            class="w-full rounded-xl border-gray-200 focus:border-emerald-600 focus:ring-emerald-600 py-3 pl-4 pr-11 text-sm text-gray-900 placeholder:text-gray-400 transition shadow-sm @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        />
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                            aria-label="Toggle password visibility"
                        >
                            <!-- Eye icon (hide) -->
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye slash icon (show) -->
                            <svg x-show="show" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Baris kecil: Checkbox "Ingat saya" saja -->
                <div class="flex items-center pt-0.5">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-600 border-gray-300 transition cursor-pointer"
                        />
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button: Penuh "Masuk" -->
                <button
                    type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition shadow-md shadow-emerald-600/20 cursor-pointer active:scale-[0.99]"
                >
                    Masuk
                </button>
            </form>

            <!-- Catatan Kaki Tipis -->
            <p class="text-xs text-center text-gray-400 mt-6">
                Aktivasi rekening nasabah baru dilakukan melalui Loket Teller.
            </p>
        </div>
    </div>
</x-guest-layout>
