<x-layouts.app title="Shift Management">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error')), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Shift Management</h2>
            <p class="page-subtitle">Kelola jadwal kerja (WFO, WFH, Flexible)</p>
        </div>
        @can('manage-shifts')
            <button type="button"
                @click="$dispatch('open-drawer', 'shift-form'); window.dispatchEvent(new CustomEvent('drawer-reset-shift'))"
                class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Shift
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th
                            class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">
                            No.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama
                            Shift</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode
                        </th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jam
                            Kerja</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Toleransi</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe
                        </th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Status</th>
                        @can('manage-shifts')
                            <th
                                class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                                Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $shift->name }}</p>
                                    @if($shift->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $shift->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ $shift->code }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-gray-800">{{ $shift->formattedHours() }}</div>
                                @if($shift->break_start && $shift->break_end)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        Istirahat: {{ substr($shift->break_start, 0, 5) }}–{{ substr($shift->break_end, 0, 5) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if(!$shift->is_flexible)
                                    <div class="text-xs text-gray-600">
                                        <span class="text-amber-600">±{{ $shift->late_tolerance_minutes }}mnt</span> terlambat
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $shift->early_leave_tolerance_minutes }}mnt early leave
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($shift->is_flexible)
                                    <span class="badge badge-blue">Flexible</span>
                                @else
                                    <span class="badge badge-gray">Fixed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($shift->is_active)
                                    <span class="badge badge-green">Aktif</span>
                                @else
                                    <span class="badge badge-gray">Nonaktif</span>
                                @endif
                            </td>
                            @can('manage-shifts')
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="$dispatch('open-drawer', 'shift-form');
                                                            window.dispatchEvent(new CustomEvent('drawer-edit-shift', {detail: {
                                                                id: '{{ $shift->id }}',
                                                                name: @js($shift->name),
                                                                code: @js($shift->code),
                                                                clock_in: '{{ substr($shift->clock_in, 0, 5) }}',
                                                                clock_out: '{{ substr($shift->clock_out, 0, 5) }}',
                                                                break_start: '{{ $shift->break_start ? substr($shift->break_start, 0, 5) : '' }}',
                                                                break_end: '{{ $shift->break_end ? substr($shift->break_end, 0, 5) : '' }}',
                                                                is_flexible: {{ $shift->is_flexible ? 'true' : 'false' }},
                                                                late_tolerance_minutes: {{ $shift->late_tolerance_minutes }},
                                                                early_leave_tolerance_minutes: {{ $shift->early_leave_tolerance_minutes }},
                                                                description: @js($shift->description ?? '')
                                                            }}))" class="btn btn-ghost btn-sm">Edit</button>

                                        <form method="POST" action="{{ route('settings.shifts.destroy', $shift) }}"
                                            id="del-shift-{{ $shift->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete(document.getElementById('del-shift-{{ $shift->id }}'), '{{ addslashes($shift->name) }}')"
                                                class="btn btn-ghost btn-sm text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                                Belum ada shift terdaftar.
                                @can('manage-shifts')
                                    <button type="button"
                                        @click="$dispatch('open-drawer', 'shift-form'); window.dispatchEvent(new CustomEvent('drawer-reset-shift'))"
                                        class="text-brand-600 hover:underline">Tambahkan yang pertama.</button>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Drawer: Create/Edit Shift --}}
    @can('manage-shifts')
        <x-drawer name="shift-form" title="Shift">
            <div x-data="{
                        editId: null,
                        name: '',
                        code: '',
                        clock_in: '09:00',
                        clock_out: '17:00',
                        break_start: '12:00',
                        break_end: '13:00',
                        is_flexible: false,
                        late_tolerance_minutes: 15,
                        early_leave_tolerance_minutes: 15,
                        description: '',
                        isEdit: false,
                        init() {
                            window.addEventListener('drawer-reset-shift', () => {
                                this.editId = null; this.name = ''; this.code = '';
                                this.clock_in = '09:00'; this.clock_out = '17:00';
                                this.break_start = '12:00'; this.break_end = '13:00';
                                this.is_flexible = false;
                                this.late_tolerance_minutes = 15;
                                this.early_leave_tolerance_minutes = 15;
                                this.description = ''; this.isEdit = false;
                            });
                            window.addEventListener('drawer-edit-shift', (e) => {
                                Object.assign(this, e.detail);
                                this.isEdit = true;
                            });
                        }
                    }">

                {{-- Create form --}}
                <template x-if="!isEdit">
                    <form method="POST" action="{{ route('settings.shifts.store') }}" class="space-y-4">
                        @csrf
                        @include('settings.shifts._form')
                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary">Tambah Shift</button>
                            <button type="button" @click="$dispatch('close-drawer', 'shift-form')"
                                class="btn btn-secondary">Batal</button>
                        </div>
                    </form>
                </template>

                {{-- Edit form --}}
                <template x-if="isEdit">
                    <div>
                        <form :action="'/settings/shifts/' + editId" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            @include('settings.shifts._form')
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <button type="button" @click="$dispatch('close-drawer', 'shift-form')"
                                    class="btn btn-secondary">Batal</button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </x-drawer>
    @endcan

</x-layouts.app>