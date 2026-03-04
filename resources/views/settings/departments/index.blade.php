<x-layouts.app :title="'Department'">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error')), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Department</h2>
            <p class="page-subtitle">Struktur departemen organisasi</p>
        </div>
        @can('system-settings')
            <button type="button"
                @click="$dispatch('open-drawer', 'dept-form'); window.dispatchEvent(new CustomEvent('drawer-reset-dept'))"
                class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Department
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama
                        </th>
                        <th
                            class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">
                            Kode</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Parent</th>
                        @can('system-settings')
                            <th
                                class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">
                                Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $dept->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="badge badge-gray">{{ $dept->code }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $dept->parent?->name ?? '—' }}</td>
                            @can('system-settings')
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="$dispatch('open-drawer', 'dept-form');
                                                    window.dispatchEvent(new CustomEvent('drawer-edit-dept', {detail: {
                                                        id: '{{ $dept->id }}',
                                                        name: @js($dept->name),
                                                        code: @js($dept->code),
                                                        parent_id: @js($dept->parent_id ?? '')
                                                    }}))" class="btn btn-ghost btn-sm">Edit</button>

                                        <form method="POST" action="{{ route('settings.departments.destroy', $dept) }}"
                                            id="del-dept-{{ $dept->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete(document.getElementById('del-dept-{{ $dept->id }}'), '{{ addslashes($dept->name) }}')"
                                                class="btn btn-ghost btn-sm text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400">Belum ada department.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Drawer: Create/Edit Department --}}
    @can('system-settings')
        {{-- Pass parent options as JSON for Alpine to pick up --}}
        @php $parentsJson = $departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code])->values(); @endphp

        <x-drawer name="dept-form" title="Department">
            <div x-data="{
                editId: null,
                name: '',
                code: '',
                parent_id: '',
                isEdit: false,
                parents: @js($parentsJson),
                init() {
                    window.addEventListener('drawer-reset-dept', () => {
                        this.editId = null; this.name = ''; this.code = ''; this.parent_id = ''; this.isEdit = false;
                    });
                    window.addEventListener('drawer-edit-dept', (e) => {
                        this.editId = e.detail.id;
                        this.name = e.detail.name;
                        this.code = e.detail.code;
                        this.parent_id = e.detail.parent_id;
                        this.isEdit = true;
                    });
                }
            }">
                <form :action="isEdit ? '/settings/departments/' + editId : '{{ route('settings.departments.store') }}'"
                    method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="isEdit ? 'PUT' : 'POST'">

                    <div>
                        <label class="form-label">Nama Department <span class="text-red-500">*</span></label>
                        <input name="name" type="text" required x-model="name" placeholder="Human Resources, Finance..."
                            class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Kode <span class="text-red-500">*</span></label>
                        <input name="code" type="text" required x-model="code" placeholder="HR, FIN, IT" class="form-input">
                        <p class="text-xs text-gray-400 mt-1">Unik, hanya huruf, angka, underscore</p>
                    </div>
                    <div>
                        <label class="form-label">Parent Department</label>
                        <select name="parent_id" x-model="parent_id" class="form-input">
                            <option value="">— Tidak ada (top-level) —</option>
                            <template x-for="p in parents.filter(p => p.id !== editId)" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (' + p.code + ')'" :selected="p.id === parent_id">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-primary"
                            x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Department'"></button>
                        <button type="button" @click="$dispatch('close-drawer', 'dept-form')"
                            class="btn btn-secondary">Batal</button>
                    </div>
                </form>
            </div>
        </x-drawer>
    @endcan

</x-layouts.app>