<x-layouts.app :title="'Job Level'">

    {{-- SweetAlert2 flash toast --}}
    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error')), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Job Level</h2>
            <p class="page-subtitle">Kelola tingkatan jabatan dalam organisasi</p>
        </div>
        @can('system-settings')
            <button type="button" @click="$dispatch('open-drawer', 'job-level-form');
                    window.dispatchEvent(new CustomEvent('drawer-reset-job-level'))" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Level
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th
                            class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-16">
                            No.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama
                            Level</th>
                        <th
                            class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                            Urutan</th>
                        @can('system-settings')
                            <th
                                class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                                Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($levels as $level)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $level->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="badge badge-blue">Level {{ $level->level }}</span>
                            </td>
                            @can('system-settings')
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit: open drawer dengan data --}}
                                        <button type="button" @click="$dispatch('open-drawer', 'job-level-form');
                                                    window.dispatchEvent(new CustomEvent('drawer-edit-job-level', {detail: {
                                                        id: '{{ $level->id }}',
                                                        name: @js($level->name),
                                                        level: {{ $level->level }}
                                                    }}))" class="btn btn-ghost btn-sm">Edit</button>

                                        {{-- Delete: SweetAlert2 confirm --}}
                                        <form method="POST" action="{{ route('settings.job-levels.destroy', $level) }}"
                                            id="del-jl-{{ $level->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete(document.getElementById('del-jl-{{ $level->id }}'), '{{ addslashes($level->name) }}')"
                                                class="btn btn-ghost btn-sm text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400">
                                Belum ada job level.
                                @can('system-settings')
                                    <button type="button"
                                        @click="$dispatch('open-drawer', 'job-level-form'); window.dispatchEvent(new CustomEvent('drawer-reset-job-level'))"
                                        class="text-brand-600 hover:underline">Tambahkan yang pertama.</button>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Drawer: Create/Edit Job Level --}}
    @can('system-settings')
        <x-drawer name="job-level-form" title="Job Level">
            <div x-data="{
                    editId: null,
                    name: '',
                    level: '',
                    isEdit: false,
                    init() {
                        window.addEventListener('drawer-reset-job-level', () => {
                            this.editId = null; this.name = ''; this.level = ''; this.isEdit = false;
                        });
                        window.addEventListener('drawer-edit-job-level', (e) => {
                            this.editId = e.detail.id;
                            this.name = e.detail.name;
                            this.level = e.detail.level;
                            this.isEdit = true;
                        });
                    }
                }">
                {{-- Create form --}}
                <template x-if="!isEdit">
                    <form method="POST" action="{{ route('settings.job-levels.store') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="form-label">Nama Level <span class="text-red-500">*</span></label>
                            <input name="name" type="text" required x-model="name"
                                placeholder="Contoh: Staff, Supervisor, Manager"
                                class="form-input @error('name') border-red-400 @enderror">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Urutan Level <span class="text-red-500">*</span></label>
                            <input name="level" type="number" min="1" max="99" required x-model="level"
                                placeholder="1 = tertinggi" class="form-input @error('level') border-red-400 @enderror">
                            <p class="text-xs text-gray-400 mt-1">Angka lebih kecil = posisi lebih tinggi</p>
                            @error('level') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary">Tambah Level</button>
                            <button type="button" @click="$dispatch('close-drawer', 'job-level-form')"
                                class="btn btn-secondary">Batal</button>
                        </div>
                    </form>
                </template>

                {{-- Edit form --}}
                <template x-if="isEdit">
                    <div>
                        <form :action="'/settings/job-levels/' + editId" method="POST" class="space-y-5">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <div>
                                <label class="form-label">Nama Level <span class="text-red-500">*</span></label>
                                <input name="name" type="text" required x-model="name" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Urutan Level <span class="text-red-500">*</span></label>
                                <input name="level" type="number" min="1" max="99" required x-model="level"
                                    class="form-input">
                                <p class="text-xs text-gray-400 mt-1">Angka lebih kecil = posisi lebih tinggi</p>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <button type="button" @click="$dispatch('close-drawer', 'job-level-form')"
                                    class="btn btn-secondary">Batal</button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </x-drawer>
    @endcan

</x-layouts.app>