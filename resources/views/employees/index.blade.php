<x-layouts.app :title="'Data Karyawan'">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error')), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Data Karyawan</h2>
            <p class="page-subtitle">{{ $employees->count() }} karyawan terdaftar</p>
        </div>
        @can('create-employees')
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Karyawan
            </a>
        @endcan
    </div>

    {{-- Search + Filter --}}
    <div class="card mb-4" x-data="{ search: '' }">
        <div class="px-5 py-3.5 flex items-center gap-3">
            <div class="relative flex-1 max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input x-model="search" type="search" placeholder="Cari nama atau nomor karyawan..."
                    class="form-input pl-9 text-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Karyawan</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">No.
                            Karyawan</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Department</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Posisi</th>
                        <th
                            class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                            Status</th>
                        <th
                            class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($employees as $emp)
                                        <tr class="hover:bg-gray-50 transition-colors"
                                            x-show="!search || '{{ strtolower($emp->full_name) }}'.includes(search.toLowerCase()) || '{{ strtolower($emp->employee_number) }}'.includes(search.toLowerCase())">
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-xs font-semibold shrink-0">
                                                        {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $emp->full_name }}</p>
                                                        <p class="text-xs text-gray-400">{{ $emp->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <span class="font-mono text-xs text-gray-600">{{ $emp->employee_number }}</span>
                                            </td>
                                            <td class="px-5 py-3.5 text-gray-600">{{ $emp->currentEmployment?->department?->name ?? '—' }}
                                            </td>
                                            <td class="px-5 py-3.5 text-gray-600">{{ $emp->currentEmployment?->position?->name ?? '—' }}
                                            </td>
                                            <td class="px-5 py-3.5">
                                                @php $status = $emp->currentEmployment?->employment_status ?? 'permanent'; @endphp
                                                <span class="badge {{ match ($status) {
                            'permanent' => 'badge-green',
                            'contract' => 'badge-blue',
                            'probation' => 'badge-yellow',
                            default => 'badge-gray'
                        } }}">{{ ucfirst($status) }}</span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('employees.show', $emp) }}" class="btn btn-ghost btn-sm">Detail</a>
                                                    <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                                        id="del-emp-{{ $emp->id }}">
                                                        @csrf @method('DELETE')
                                                        <button type="button"
                                                            onclick="confirmDelete(document.getElementById('del-emp-{{ $emp->id }}'), '{{ addslashes($emp->full_name) }}')"
                                                            class="btn btn-ghost btn-sm text-red-600 hover:bg-red-50">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197" />
                                    </svg>
                                    <p>Belum ada karyawan terdaftar.</p>
                                    @can('create-employees')
                                        <a href="{{ route('employees.create') }}"
                                            class="text-brand-600 hover:underline text-sm">Tambah karyawan pertama</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>