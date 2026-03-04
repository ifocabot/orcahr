<x-layouts.app :title="'Posisi Jabatan'">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error')), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Posisi Jabatan</h2>
            <p class="page-subtitle">Daftar posisi dalam setiap departemen</p>
        </div>
        @can('system-settings')
            <button type="button"
                @click="$dispatch('open-drawer', 'pos-form'); window.dispatchEvent(new CustomEvent('drawer-reset-pos'))"
                class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Posisi
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama
                            Posisi</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Department</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Job
                            Level</th>
                        @can('system-settings')
                            <th
                                class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                                Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($positions as $pos)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $pos->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $pos->department->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="badge badge-blue">{{ $pos->jobLevel->name }}</span>
                            </td>
                            @can('system-settings')
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="$dispatch('open-drawer', 'pos-form');
                                                    window.dispatchEvent(new CustomEvent('drawer-edit-pos', {detail: {
                                                        id: '{{ $pos->id }}',
                                                        name: @js($pos->name),
                                                        department_id: '{{ $pos->department_id }}',
                                                        job_level_id: '{{ $pos->job_level_id }}'
                                                    }}))" class="btn btn-ghost btn-sm">Edit</button>

                                        <form method="POST" action="{{ route('settings.positions.destroy', $pos) }}"
                                            id="del-pos-{{ $pos->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete(document.getElementById('del-pos-{{ $pos->id }}'), '{{ addslashes($pos->name) }}')"
                                                class="btn btn-ghost btn-sm text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400">Belum ada posisi jabatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Drawer: Create/Edit Posisi --}}
    @can('system-settings')
        @php
            $depts = $positions->pluck('department')->unique('id')->values()
                ->merge(\App\Models\Department::orderBy('name')->get(['id', 'name']))
                ->unique('id')->values();
            $levels = \App\Models\JobLevel::orderBy('level')->get(['id', 'name', 'level']);
        @endphp

        <x-drawer name="pos-form" title="Posisi Jabatan">
            <div x-data="{
                editId: null,
                name: '',
                department_id: '',
                job_level_id: '',
                isEdit: false,
                departments: @js($depts->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->values()),
                jobLevels: @js($levels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'level' => $l->level])->values()),
                init() {
                    window.addEventListener('drawer-reset-pos', () => {
                        this.editId = null; this.name = ''; this.department_id = ''; this.job_level_id = ''; this.isEdit = false;
                    });
                    window.addEventListener('drawer-edit-pos', (e) => {
                        this.editId = e.detail.id;
                        this.name = e.detail.name;
                        this.department_id = e.detail.department_id;
                        this.job_level_id = e.detail.job_level_id;
                        this.isEdit = true;
                    });
                }
            }">
                <form :action="isEdit ? '/settings/positions/' + editId : '{{ route('settings.positions.store') }}'"
                    method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="isEdit ? 'PUT' : 'POST'">

                    <div>
                        <label class="form-label">Nama Posisi <span class="text-red-500">*</span></label>
                        <input name="name" type="text" required x-model="name"
                            placeholder="HR Manager, Software Engineer..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" x-model="department_id" required class="form-input">
                            <option value="">— Pilih Department —</option>
                            <template x-for="d in departments" :key="d.id">
                                <option :value="d.id" x-text="d.name" :selected="d.id === department_id"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Job Level <span class="text-red-500">*</span></label>
                        <select name="job_level_id" x-model="job_level_id" required class="form-input">
                            <option value="">— Pilih Level —</option>
                            <template x-for="l in jobLevels" :key="l.id">
                                <option :value="l.id" x-bind:text="l.name + ' (Level ' + l.level + ')'"
                                    :selected="l.id === job_level_id"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-primary"
                            x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Posisi'"></button>
                        <button type="button" @click="$dispatch('close-drawer', 'pos-form')"
                            class="btn btn-secondary">Batal</button>
                    </div>
                </form>
            </div>
        </x-drawer>
    @endcan

</x-layouts.app>