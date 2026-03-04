{{-- Employee Documents Partial --}}
<section class="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--color-border)]">
        <div>
            <h3 class="text-base font-semibold text-[var(--color-text)]">Dokumen Karyawan</h3>
            <p class="text-xs text-[var(--color-text-muted)] mt-0.5">{{ $employee->documents->count() }} dokumen
                tersimpan</p>
        </div>
        @can('update', $employee)
            <button x-data @click="$dispatch('open-drawer', { id: 'drawer-doc-upload' })" class="btn-primary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload Dokumen
            </button>
        @endcan
    </div>

    {{-- Document Table --}}
    @if($employee->documents->isEmpty())
        <div class="p-10 text-center">
            <svg class="w-10 h-10 mx-auto text-[var(--color-text-muted)] mb-3" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm text-[var(--color-text-muted)]">Belum ada dokumen tersimpan</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-border)] bg-[var(--color-surface-hover)]">
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Nama File</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Tipe</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Keterangan</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Expired</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Upload</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    @foreach($employee->documents as $doc)
                        <tr class="hover:bg-[var(--color-surface-hover)] transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[var(--color-text-muted)] shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-[var(--color-text)] font-medium truncate max-w-[200px]"
                                        title="{{ $doc->original_name }}">
                                        {{ $doc->original_name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                                    {{ $doc->typeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-[var(--color-text-muted)]">
                                {{ $doc->notes ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($doc->expires_at)
                                    @if($doc->isExpired())
                                        <span class="text-xs text-red-400 font-medium">
                                            ⚠ {{ $doc->expires_at->format('d M Y') }}
                                        </span>
                                    @elseif($doc->isExpiringSoon())
                                        <span class="text-xs text-yellow-400 font-medium">
                                            ⚡ {{ $doc->expires_at->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-[var(--color-text-muted)]">
                                            {{ $doc->expires_at->format('d M Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[var(--color-text-muted)]">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-[var(--color-text-muted)]">
                                {{ $doc->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('employees.documents.download', [$employee, $doc]) }}"
                                        class="text-[var(--color-primary)] hover:text-[var(--color-primary-hover)] text-xs font-medium transition-colors">
                                        Download
                                    </a>
                                    @can('update', $employee)
                                        <span class="text-[var(--color-border)]">|</span>
                                        <button type="button"
                                            class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors"
                                            onclick="confirmDeleteDocument('{{ route('employees.documents.destroy', [$employee, $doc]) }}')">
                                            Hapus
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

{{-- Upload Drawer --}}
@can('update', $employee)
    <x-drawer id="drawer-doc-upload" title="Upload Dokumen" size="md">
        <form action="{{ route('employees.documents.store', $employee) }}" method="POST" enctype="multipart/form-data"
            class="space-y-5">
            @csrf

            {{-- File --}}
            <div>
                <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                    File <span class="text-red-400">*</span>
                </label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required
                    class="block w-full text-sm text-[var(--color-text-muted)] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[var(--color-primary)] file:text-white hover:file:bg-[var(--color-primary-hover)] file:cursor-pointer cursor-pointer">
                <p class="text-xs text-[var(--color-text-muted)] mt-1">PDF, JPG, PNG, DOC, DOCX — maks. 5 MB</p>
            </div>

            {{-- Tipe --}}
            <div>
                <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                    Tipe Dokumen <span class="text-red-400">*</span>
                </label>
                <select name="type" required class="form-select w-full">
                    <option value="">— Pilih Tipe —</option>
                    @foreach(\App\Models\EmployeeDocument::$typeLabels as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Expires At --}}
            <div>
                <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                    Tanggal Expired <span class="text-xs text-[var(--color-text-muted)] font-normal">(opsional)</span>
                </label>
                <input type="date" name="expires_at" class="form-input w-full">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                    Keterangan <span class="text-xs text-[var(--color-text-muted)] font-normal">(opsional)</span>
                </label>
                <input type="text" name="notes" placeholder="Mis: Kontrak Kerja Periode 2024-2025" maxlength="255"
                    class="form-input w-full">
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload
                </button>
                <button type="button" @click="$dispatch('close-drawer', { id: 'drawer-doc-upload' })"
                    class="btn-secondary flex-1">Batal</button>
            </div>
        </form>
    </x-drawer>

    <script>
        function confirmDeleteDocument(url) {
            window.confirmDelete('Hapus dokumen ini?', 'File akan dihapus permanen.', url);
        }
    </script>
@endcan