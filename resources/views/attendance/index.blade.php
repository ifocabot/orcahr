<x-layouts.app title="Rekap Absensi">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Rekap Absensi</h2>
            <p class="page-subtitle">Data kehadiran karyawan per bulan</p>
        </div>
        @can('manage-attendance')
            <div class="flex gap-2">
                {{-- Filter Bulan --}}
                <form method="GET" action="{{ route('attendance.index') }}" class="flex gap-2">
                    <select name="month" onchange="this.form.submit()" class="form-input py-2 px-3 text-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" onchange="this.form.submit()" class="form-input py-2 px-3 text-sm">
                        @foreach(range(now()->year - 1, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endcan
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead class="table-head">
                    <tr>
                        <th class="table-th">Karyawan</th>
                        <th class="table-th text-center">Hadir</th>
                        <th class="table-th text-center">Terlambat</th>
                        <th class="table-th text-center">Absen</th>
                        <th class="table-th text-center">Cuti</th>
                        <th class="table-th text-center">Total Jam</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($attendances as $data)
                        <tr class="table-row">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold shrink-0">
                                        {{ strtoupper(substr($data['employee']->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $data['employee']->full_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $data['employee']->employee_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td text-center">
                                <span class="badge badge-green">{{ $data['present'] }}</span>
                            </td>
                            <td class="table-td text-center">
                                @if($data['late'] > 0)
                                    <span class="badge badge-yellow">{{ $data['late'] }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                @if($data['absent'] > 0)
                                    <span class="badge badge-red">{{ $data['absent'] }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                @if($data['leave'] > 0)
                                    <span class="badge badge-blue">{{ $data['leave'] }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="table-td text-center">
                                <span
                                    class="text-sm font-medium text-gray-700">{{ number_format($data['work_hours'], 1) }}j</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td text-center text-gray-400 py-10">
                                Belum ada data absensi untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>