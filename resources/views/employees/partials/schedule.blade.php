{{-- Schedule Assignment Partial --}}
<section class="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--color-border)]">
        <div>
            <h3 class="text-base font-semibold text-[var(--color-text)]">Jadwal Kerja (Shift)</h3>
            <p class="text-xs text-[var(--color-text-muted)] mt-0.5">
                @if($employee->currentSchedule)
                    Shift aktif: <span class="font-medium text-[var(--color-primary)]">{{ $employee->currentSchedule->shift->name }}</span>
                    ({{ $employee->currentSchedule->shift->formattedHours() }})
                @else
                    Belum ada shift di-assign
                @endif
            </p>
        </div>
        @can('update', $employee)
            <button
                x-data
                @click="$dispatch('open-drawer', { id: 'drawer-schedule-assign' })"
                class="btn-primary text-sm"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Assign Shift
            </button>
        @endcan
    </div>

    {{-- Schedule History Table --}}
    @if($employee->scheduleHistory->isEmpty())
        <div class="p-10 text-center">
            <svg class="w-10 h-10 mx-auto text-[var(--color-text-muted)] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-[var(--color-text-muted)]">Belum ada jadwal shift</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-border)] bg-[var(--color-surface-hover)]">
                        <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Jam Kerja</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Berlaku Dari</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Berlaku Sampai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Status</th>
                        @can('update', $employee)
                            <th class="px-6 py-3 text-right text-xs font-medium text-[var(--color-text-muted)] uppercase tracking-wider">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    @foreach($employee->scheduleHistory as $schedule)
                        <tr class="hover:bg-[var(--color-surface-hover)] transition-colors {{ $schedule->isActive() ? 'bg-green-50/5' : '' }}">
                            <td class="px-6 py-3">
                                <div class="font-medium text-[var(--color-text)]">{{ $schedule->shift->name }}</div>
                                <div class="text-xs text-[var(--color-text-muted)] font-mono">{{ $schedule->shift->code }}</div>
                            </td>
                            <td class="px-6 py-3 text-[var(--color-text-muted)]">
                                {{ $schedule->shift->formattedHours() }}
                            </td>
                            <td class="px-6 py-3 text-[var(--color-text-muted)]">
                                {{ $schedule->effective_from->format('d M Y') }}
                            </td>
                            <td class="px-6 py-3 text-[var(--color-text-muted)]">
                                {{ $schedule->effective_to?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($schedule->isActive())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            @can('update', $employee)
                                <td class="px-6 py-3 text-right">
                                    @if(!$schedule->isActive())
                                        <form method="POST"
                                            action="{{ route('employees.schedules.destroy', [$employee, $schedule]) }}"
                                            id="del-sched-{{ $schedule->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete(document.getElementById('del-sched-{{ $schedule->id }}'), 'riwayat shift ini')"
                                                class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-[var(--color-text-muted)]">—</span>
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

{{-- Assign Shift Drawer --}}
@can('update', $employee)
<x-drawer id="drawer-schedule-assign" title="Assign Shift" size="md">
    <form
        action="{{ route('employees.schedules.store', $employee) }}"
        method="POST"
        class="space-y-5"
    >
        @csrf

        {{-- Pilih Shift --}}
        <div>
            <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                Shift <span class="text-red-400">*</span>
            </label>
            <select name="shift_id" required class="form-select w-full">
                <option value="">— Pilih Shift —</option>
                @foreach(\App\Models\Shift::where('is_active', true)->orderBy('clock_in')->get() as $shift)
                    <option value="{{ $shift->id }}">
                        {{ $shift->name }} ({{ $shift->formattedHours() }})
                        {{ $shift->is_flexible ? '— Flexible' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Berlaku Dari --}}
        <div>
            <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                Berlaku Mulai <span class="text-red-400">*</span>
            </label>
            <input type="date" name="effective_from" required
                value="{{ date('Y-m-d') }}"
                class="form-input w-full">
            <p class="text-xs text-[var(--color-text-muted)] mt-1">
                Schedule aktif yang ada akan ditutup otomatis di hari sebelumnya.
            </p>
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-[var(--color-text)] mb-1.5">
                Keterangan <span class="text-xs text-[var(--color-text-muted)] font-normal">(opsional)</span>
            </label>
            <input type="text" name="notes" placeholder="Mis: Pindah ke WFH sementara"
                maxlength="255" class="form-input w-full">
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary flex-1">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Assign Shift
            </button>
            <button type="button" @click="$dispatch('close-drawer', { id: 'drawer-schedule-assign' })"
                class="btn-secondary flex-1">Batal</button>
        </div>
    </form>
</x-drawer>
@endcan
