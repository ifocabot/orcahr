<x-layouts.auth>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-sm">

            {{-- Logo --}}
            <div class="text-center mb-8">
                <div
                    class="inline-flex w-14 h-14 rounded-2xl bg-brand-600 items-center justify-center mb-4 shadow-lg shadow-brand-900/40">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">OrcaHR</h1>
                <p class="text-sm text-gray-400 mt-1">Human Resource System</p>
            </div>

            {{-- Card --}}
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-8 shadow-2xl">
                <h2 class="text-base font-semibold text-white mb-6">Masuk ke akun Anda</h2>

                {{-- Session Error --}}
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-900/40 border border-red-800/60 rounded-lg">
                        <p class="text-sm text-red-300">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}" placeholder="nama@perusahaan.com"
                            class="w-full px-3.5 py-2.5 text-sm bg-white/8 border border-white/12 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 text-sm bg-white/8 border border-white/12 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" id="remember_me"
                                class="w-4 h-4 rounded border-white/20 bg-white/8 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-gray-400">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-transparent shadow-sm mt-2">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-gray-600 mt-6">
                © {{ date('Y') }} OrcaHR · Data dilindungi ISO 27001
            </p>
        </div>
    </div>
</x-layouts.auth>