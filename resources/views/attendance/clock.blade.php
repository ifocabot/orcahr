<x-layouts.app title="Absensi Hari Ini">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error') || $errors->any())
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error') ?? $errors->first()), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Absensi</h2>
            <p class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

    @if(!$employee)
        {{-- Tidak ada employee terkait dengan user ini --}}
        <div class="card p-10 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <p class="text-gray-500 font-medium">Akun Anda belum terhubung ke data karyawan.</p>
            <p class="text-sm text-gray-400 mt-1">Hubungi HR Admin untuk menghubungkan akun.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Kolom Kiri: Clock In/Out Card --}}
            <div class="lg:col-span-1">
                <div class="card p-6 text-center space-y-6">

                    {{-- Jam Digital --}}
                    <div x-data="{ time: '' }" x-init="
                                function tick() {
                                    const now = new Date();
                                    time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                                }
                                tick();
                                setInterval(tick, 1000);
                            ">
                        <p x-text="time" class="text-5xl font-bold font-mono tracking-widest text-gray-900"></p>
                        <p class="text-sm text-gray-400 mt-1">{{ now()->format('d M Y') }}</p>
                    </div>

                    {{-- Status Badge --}}
                    <div>
                        @if($status === 'not_started')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-600 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                Belum Absen
                            </span>
                        @elseif($status === 'clocked_in')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Sudah Clock In
                            </span>
                        @elseif($status === 'clocked_out')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Selesai Hari Ini
                            </span>
                        @endif
                    </div>

                    {{-- Clock In/Out Info --}}
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-1">Clock In</p>
                            <p class="font-semibold text-gray-800 text-lg">
                                {{ $clockIn ? $clockIn->timestamp->format('H:i') : '—' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-1">Clock Out</p>
                            <p class="font-semibold text-gray-800 text-lg">
                                {{ $clockOut ? $clockOut->timestamp->format('H:i') : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <form action="{{ route('attendance.clock.store') }}" method="POST">
                        @csrf
                        @if($status === 'not_started')
                            <input type="hidden" name="action" value="clock_in">
                            <button type="submit"
                                class="w-full py-3 px-6 rounded-xl bg-green-500 hover:bg-green-600 active:bg-green-700 text-white font-semibold text-base transition-all duration-150 shadow-sm hover:shadow-md">
                                🟢 Clock In Sekarang
                            </button>
                        @elseif($status === 'clocked_in')
                            <input type="hidden" name="action" value="clock_out">
                            <button type="submit"
                                class="w-full py-3 px-6 rounded-xl bg-red-500 hover:bg-red-600 active:bg-red-700 text-white font-semibold text-base transition-all duration-150 shadow-sm hover:shadow-md">
                                🔴 Clock Out Sekarang
                            </button>
                        @else
                            <button type="button" disabled
                                class="w-full py-3 px-6 rounded-xl bg-gray-200 text-gray-400 font-semibold text-base cursor-not-allowed">
                                ✅ Absensi Selesai
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: Info Shift + Karyawan --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Info Karyawan --}}
                <div class="card p-5">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-lg font-semibold shrink-0">
                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $employee->full_name }}</p>
                            <p class="text-sm text-gray-400">{{ $employee->employee_number }}</p>
                            @if($employee->currentEmployment)
                                <p class="text-xs text-gray-400">
                                    {{ $employee->currentEmployment->position->name }}
                                    · {{ $employee->currentEmployment->department->name }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Info Shift Hari Ini --}}
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Shift Hari Ini</h3>
                    @if($todayShift)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Nama Shift</p>
                                <p class="font-medium text-gray-800">{{ $todayShift->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Jam Masuk</p>
                                <p class="font-medium text-gray-800">{{ substr($todayShift->clock_in, 0, 5) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Jam Pulang</p>
                                <p class="font-medium text-gray-800">{{ substr($todayShift->clock_out, 0, 5) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Tipe</p>
                                <p>
                                    @if($todayShift->is_flexible)
                                        <span class="badge badge-blue">Flexible</span>
                                    @else
                                        <span class="badge badge-gray">Fixed</span>
                                        <span class="text-xs text-amber-600 block mt-0.5">Toleransi
                                            ±{{ $todayShift->late_tolerance_minutes }} mnt</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-400">Tidak ada jadwal shift untuk hari ini.</p>
                            <p class="text-xs text-gray-300 mt-1">Hubungi HR untuk assign jadwal.</p>
                        </div>
                    @endif
                </div>

                {{-- Log Hari Ini (jika ada) --}}
                @if($clockIn || $clockOut)
                    <div class="card p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Log Absensi Hari Ini</h3>
                        <div class="space-y-2">
                            @if($clockIn)
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                                    <span class="text-gray-500 w-16">Clock In</span>
                                    <span class="font-medium text-gray-800">{{ $clockIn->timestamp->format('H:i:s') }}</span>
                                    @if($todayShift && !$todayShift->is_flexible)
                                        @php
                                            $shiftIn = \Carbon\Carbon::parse(today()->format('Y-m-d') . ' ' . $todayShift->clock_in);
                                            $lateMinutes = max(0, $clockIn->timestamp->diffInMinutes($shiftIn, false) * -1);
                                        @endphp
                                        @if($lateMinutes > $todayShift->late_tolerance_minutes)
                                            <span class="badge badge-yellow text-xs">Terlambat {{ $lateMinutes }} mnt</span>
                                        @else
                                            <span class="badge badge-green text-xs">Tepat Waktu</span>
                                        @endif
                                    @endif
                                    <span class="text-gray-300 text-xs ml-auto">via {{ $clockIn->source }}</span>
                                </div>
                            @endif
                            @if($clockOut)
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                    <span class="text-gray-500 w-16">Clock Out</span>
                                    <span class="font-medium text-gray-800">{{ $clockOut->timestamp->format('H:i:s') }}</span>
                                    @if($clockIn)
                                        @php $workDuration = $clockIn->timestamp->diff($clockOut->timestamp); @endphp
                                        <span class="text-xs text-gray-400">
                                            {{ $workDuration->h }}j {{ $workDuration->i }}m kerja
                                        </span>
                                    @endif
                                    <span class="text-gray-300 text-xs ml-auto">via {{ $clockOut->source }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif

</x-layouts.app>