<x-layouts.app title="Hari Libur">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Kalender Hari Libur</h2>
            <p class="page-subtitle">Daftar hari libur nasional dan perusahaan</p>
        </div>
        @can('manage-holidays')
            <button onclick="document.getElementById('drawer-add').classList.remove('hidden')"
                class="btn btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Hari Libur
            </button>
        @endcan
    </div>

    @forelse($holidays as $year => $yearHolidays)
        <div class="card mb-5" x-data="{ open: {{ $year == now()->year ? 'true' : 'false' }} }">
            <div class="card-header cursor-pointer flex items-center justify-between" @click="open = !open">
                <h3 class="font-semibold text-gray-700">{{ $year }}
                    <span class="text-xs text-gray-400 font-normal ml-2">({{ $yearHolidays->count() }} hari)</span>
                </h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            <div x-show="open" x-collapse>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th class="table-th">Tanggal</th>
                                <th class="table-th">Hari</th>
                                <th class="table-th">Nama Hari Libur</th>
                                <th class="table-th text-center">Tipe</th>
                                @can('manage-holidays')
                                    <th class="table-th text-right">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @foreach($yearHolidays as $holiday)
                                <tr class="table-row">
                                    <td class="table-td font-medium text-gray-800">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('d M Y') }}
                                    </td>
                                    <td class="table-td text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('l') }}
                                    </td>
                                    <td class="table-td text-sm">{{ $holiday->name }}</td>
                                    <td class="table-td text-center">
                                        @if($holiday->is_national)
                                            <span class="badge badge-red">Nasional</span>
                                        @else
                                            <span class="badge badge-blue">Perusahaan</span>
                                        @endif
                                    </td>
                                    @can('manage-holidays')
                                        <td class="table-td text-right">
                                            <button
                                                onclick="window.confirmDelete('Hapus hari libur ini?', '{{ route('settings.holidays.destroy', $holiday) }}', 'DELETE')"
                                                class="text-red-400 hover:text-red-600 text-xs transition-colors">
                                                Hapus
                                            </button>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card p-10 text-center">
            <p class="text-gray-400">Belum ada data hari libur.</p>
        </div>
    @endforelse

    {{-- Drawer Tambah --}}
    @can('manage-holidays')
        <div id="drawer-add" class="hidden fixed inset-0 z-50 flex justify-end"
            @keydown.escape.window="document.getElementById('drawer-add').classList.add('hidden')">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"
                onclick="document.getElementById('drawer-add').classList.add('hidden')"></div>

            <div class="relative bg-white w-full max-w-md h-full shadow-2xl flex flex-col">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Tambah Hari Libur</h2>
                    <button onclick="document.getElementById('drawer-add').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('settings.holidays.store') }}" method="POST" class="flex-1 p-5 space-y-5">
                    @csrf
                    <div>
                        <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Nama Hari Libur <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Contoh: Hari Raya Idul Fitri"
                            required>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_national" value="1" checked
                                class="w-4 h-4 rounded border-gray-300 text-brand-600">
                            <span class="text-sm text-gray-700">Hari libur nasional</span>
                        </label>
                    </div>
                    <div class="pt-4 border-t border-gray-100 flex gap-3">
                        <button type="submit" class="btn btn-primary flex-1">Tambah</button>
                        <button type="button" onclick="document.getElementById('drawer-add').classList.add('hidden')"
                            class="btn btn-secondary">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

</x-layouts.app>