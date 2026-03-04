<x-layouts.app :title="$employee->full_name">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif

    <div class="page-header">
        <div class="flex items-center gap-4">
            {{-- Avatar --}}
            <div
                class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-lg font-semibold shrink-0 ring-2 ring-white shadow">
                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="page-title">{{ $employee->full_name }}</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="font-mono text-xs text-gray-400">{{ $employee->employee_number }}</span>
                    @if($employee->currentEmployment)
                                        <span class="text-gray-300">·</span>
                                        <span class="badge {{ match ($employee->currentEmployment->employment_status) {
                            'permanent' => 'badge-green',
                            'contract' => 'badge-blue',
                            'probation' => 'badge-yellow',
                            default => 'badge-gray'
                        } }}">{{ ucfirst($employee->currentEmployment->employment_status) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            @can('edit-employees')
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary">Edit</a>
            @endcan
            <a href="{{ route('employees.index') }}" class="btn btn-ghost">← Kembali</a>
        </div>
    </div>

    {{-- Grid layout: 2 kol --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Kolom kiri: info pribadi + info sensitif --}}
        <div class="col-span-3 lg:col-span-2 space-y-5">

            {{-- Info Pribadi --}}
            <div class="card">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Pribadi</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Email Kantor</p>
                        <p class="font-medium text-gray-900">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Jenis Kelamin</p>
                        <p class="font-medium text-gray-900">
                            {{ $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '—') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Tempat, Tanggal Lahir</p>
                        <p class="font-medium text-gray-900">
                            @if($employee->birth_date)
                                {{ $employee->birth_date->format('d F Y') }}
                            @else —
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Status Pernikahan</p>
                        <p class="font-medium text-gray-900">{{ match ($employee->marital_status) {
    'single' => 'Belum Menikah',
    'married' => 'Menikah',
    'divorced' => 'Cerai Hidup',
    'widowed' => 'Cerai Mati',
    default => '—'
} }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Agama</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($employee->religion ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Golongan Darah</p>
                        <p class="font-medium text-gray-900">{{ $employee->blood_type ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Info Sensitif (RBAC-gated) --}}
            @can('view-sensitive-data')
                <div class="card">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Data Sensitif</h3>
                        <span class="badge badge-yellow text-xs">🔒 Terenkripsi</span>
                    </div>
                    <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">NIK / KTP</p>
                            <p class="font-medium font-mono text-gray-900">{{ $employee->nik ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">NPWP</p>
                            <p class="font-medium font-mono text-gray-900">{{ $employee->npwp ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">No. HP</p>
                            <p class="font-medium text-gray-900">{{ $employee->phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Email Pribadi</p>
                            <p class="font-medium text-gray-900">{{ $employee->personal_email ?? '—' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400 mb-0.5">Alamat</p>
                            <p class="font-medium text-gray-900 whitespace-pre-wrap">{{ $employee->address ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- Riwayat Jabatan --}}
            @can('view-employment-history')
                <div class="card">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Riwayat Jabatan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                        Department</th>
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                        Posisi</th>
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                        Mulai</th>
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                        Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($employee->employments->sortByDesc('effective_from') as $emp)
                                    <tr class="{{ is_null($emp->effective_to) ? 'bg-green-50/50' : '' }}">
                                        <td class="px-5 py-3 text-gray-800">{{ $emp->department->name }}</td>
                                        <td class="px-5 py-3 text-gray-800">{{ $emp->position->name }}</td>
                                        <td class="px-5 py-3 text-gray-500">{{ $emp->effective_from->format('d M Y') }}</td>
                                        <td class="px-5 py-3">
                                            @if(is_null($emp->effective_to))
                                                <span class="badge badge-green">Aktif</span>
                                            @else
                                                <span class="text-gray-400">{{ $emp->effective_to->format('d M Y') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-6 text-center text-gray-400">Tidak ada riwayat jabatan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan

        </div>

        {{-- Kolom kanan: info pekerjaan + bank + BPJS --}}
        <div class="col-span-3 lg:col-span-1 space-y-5">

            {{-- Info Pekerjaan (Current) --}}
            <div class="card">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Info Pekerjaan</h3>
                </div>
                <div class="px-5 py-4 space-y-3 text-sm">
                    @if($employee->currentEmployment)
                        <div>
                            <p class="text-xs text-gray-400">Department</p>
                            <p class="font-medium text-gray-900">{{ $employee->currentEmployment->department->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Posisi</p>
                            <p class="font-medium text-gray-900">{{ $employee->currentEmployment->position->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Job Level</p>
                            <p class="font-medium text-gray-900">{{ $employee->currentEmployment->jobLevel->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tanggal Masuk</p>
                            <p class="font-medium text-gray-900">
                                {{ $employee->currentEmployment->join_date->format('d F Y') }}
                            </p>
                        </div>
                        @if($employee->currentEmployment->end_date)
                            <div>
                                <p class="text-xs text-gray-400">Tanggal Berakhir</p>
                                <p class="font-medium text-gray-900 text-amber-600">
                                    {{ $employee->currentEmployment->end_date->format('d F Y') }}
                                </p>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-400 text-xs">Tidak ada data pekerjaan aktif.</p>
                    @endif
                </div>
            </div>

            {{-- Rekening Bank --}}
            @can('manage-bank-accounts')
                <div class="card">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Rekening Bank</h3>
                    </div>
                    @if($employee->primaryBank)
                        <div class="px-5 py-4 space-y-2 text-sm">
                            <div>
                                <p class="text-xs text-gray-400">Bank</p>
                                <p class="font-medium text-gray-900">{{ $employee->primaryBank->bank_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">No. Rekening</p>
                                <p class="font-medium font-mono text-gray-900">{{ $employee->primaryBank->account_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Atas Nama</p>
                                <p class="font-medium text-gray-900">{{ $employee->primaryBank->account_holder }}</p>
                            </div>
                        </div>
                    @else
                        <p class="px-5 py-4 text-xs text-gray-400">Belum ada rekening bank.</p>
                    @endif
                </div>
            @endcan

            {{-- BPJS --}}
            @can('manage-bpjs')
                <div class="card">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">BPJS</h3>
                    </div>
                    @if($employee->bpjs)
                        <div class="px-5 py-4 space-y-2 text-sm">
                            <div>
                                <p class="text-xs text-gray-400">BPJS Kesehatan</p>
                                <p class="font-medium font-mono text-gray-900">{{ $employee->bpjs->bpjs_kes ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">BPJS Ketenagakerjaan</p>
                                <p class="font-medium font-mono text-gray-900">{{ $employee->bpjs->bpjs_tk ?? '—' }}</p>
                            </div>
                            @if($employee->bpjs->bpjs_class)
                                <div>
                                    <p class="text-xs text-gray-400">Kelas</p>
                                    <p class="font-medium text-gray-900">Kelas {{ $employee->bpjs->bpjs_class }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="px-5 py-4 text-xs text-gray-400">Data BPJS belum diisi.</p>
                    @endif
                </div>
            @endcan

        </div>
    </div>

    {{-- Jadwal Kerja (Shift) --}}
    <div class="mt-5">
        @include('employees.partials.schedule')
    </div>

    {{-- Dokumen Karyawan --}}
    <div class="mt-5">
        @include('employees.partials.documents')
    </div>

</x-layouts.app>