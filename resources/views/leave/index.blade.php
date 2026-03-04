<x-layouts.app title="Cuti Saya">

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('success'))));</script>
    @endif
    @if(session('error') || $errors->any())
        <script>document.addEventListener('DOMContentLoaded', () => toast(@js(session('error') ?? $errors->first()), 'error'));</script>
    @endif

    <div class="page-header">
        <div>
            <h2 class="page-title">Cuti</h2>
            <p class="page-subtitle">Pengajuan &amp; saldo cuti Anda</p>
        </div>
        <button onclick="document.getElementById('drawer-cuti').classList.remove('hidden')"
            class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajukan Cuti
        </button>
    </div>

    {{-- Saldo Cuti --}}
    @if($balances->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($balances as $balance)
                <div class="card p-4">
                    <p class="text-xs text-gray-400 mb-1">{{ $balance->leaveType->name }}</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold text-gray-900">{{ $balance->remaining() }}</span>
                        <span class="text-sm text-gray-400 pb-1">/ {{ $balance->total_quota }} hari</span>
                    </div>
                    <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full transition-all"
                            style="width: {{ min(100, $balance->usedPercentage()) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">{{ $balance->used }} terpakai · {{ $balance->pending }} pending</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Riwayat Permohonan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Permohonan Cuti</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead class="table-head">
                    <tr>
                        <th class="table-th">Tipe Cuti</th>
                        <th class="table-th">Tanggal</th>
                        <th class="table-th text-center">Durasi</th>
                        <th class="table-th">Alasan</th>
                        <th class="table-th text-center">Status</th>
                        <th class="table-th">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($requests as $req)
                        <tr class="table-row">
                            <td class="table-td">
                                <span class="font-medium text-gray-800 text-sm">{{ $req->leaveType->name }}</span>
                            </td>
                            <td class="table-td text-sm">
                                {{ $req->start_date->format('d M Y') }}
                                @if($req->start_date->ne($req->end_date))
                                    — {{ $req->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="table-td text-center">
                                <span class="font-medium">{{ $req->total_days }}</span>
                                <span class="text-gray-400 text-xs">hari</span>
                            </td>
                            <td class="table-td text-sm text-gray-600">
                                {{ Str::limit($req->reason, 50) }}
                            </td>
                            <td class="table-td text-center">
                                @php
                                    $colors = ['pending' => 'badge-yellow', 'approved' => 'badge-green', 'rejected' => 'badge-red', 'cancelled' => 'badge-gray'];
                                    $labels = ['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
                                @endphp
                                <span class="badge {{ $colors[$req->status] ?? 'badge-gray' }}">
                                    {{ $labels[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td class="table-td text-sm text-gray-500">
                                {{ $req->approver?->name ?? '—' }}
                                @if($req->approved_at)
                                    <span class="block text-xs text-gray-300">{{ $req->approved_at->format('d M H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td text-center text-gray-400 py-10">
                                Belum ada permohonan cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    {{-- Drawer Ajukan Cuti --}}
    <div id="drawer-cuti" class="hidden fixed inset-0 z-50 flex justify-end" x-data="{ open: false }"
        x-init="$nextTick(() => { if (!document.getElementById('drawer-cuti').classList.contains('hidden')) open = true })"
        @keydown.escape.window="document.getElementById('drawer-cuti').classList.add('hidden')">

        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"
            onclick="document.getElementById('drawer-cuti').classList.add('hidden')"></div>

        <div class="relative bg-white w-full max-w-md h-full shadow-2xl flex flex-col overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Ajukan Cuti</h2>
                <button onclick="document.getElementById('drawer-cuti').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data"
                class="flex-1 p-5 space-y-5">
                @csrf

                <div>
                    <label class="form-label">Tipe Cuti <span class="text-red-500">*</span></label>
                    <select name="leave_type_id" class="form-input" required>
                        <option value="">— Pilih Tipe Cuti —</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->default_quota }} hari/tahun)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" class="form-input" min="{{ now()->toDateString() }}"
                            required>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" class="form-input" min="{{ now()->toDateString() }}"
                            required>
                    </div>
                </div>

                <div>
                    <label class="form-label">Alasan <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" class="form-input" required
                        placeholder="Tuliskan alasan pengajuan cuti..."></textarea>
                </div>

                <div>
                    <label class="form-label">Lampiran
                        <span class="text-gray-400 text-xs">(PDF/JPG/PNG, maks 5MB)</span>
                    </label>
                    <input type="file" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="pt-4 border-t border-gray-100 flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1">Ajukan Cuti</button>
                    <button type="button" onclick="document.getElementById('drawer-cuti').classList.add('hidden')"
                        class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>