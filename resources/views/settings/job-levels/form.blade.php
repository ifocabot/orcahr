<x-layouts.app :title="$jobLevel ? 'Edit Job Level' : 'Tambah Job Level'">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $jobLevel ? 'Edit Job Level' : 'Tambah Job Level' }}</h2>
            <p class="page-subtitle">Tingkatan jabatan dalam struktur organisasi</p>
        </div>
        <a href="{{ route('settings.job-levels.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div class="card max-w-md">
        <div class="card-body">
            <form method="POST"
                action="{{ $jobLevel ? route('settings.job-levels.update', $jobLevel) : route('settings.job-levels.store') }}">
                @csrf
                @if($jobLevel) @method('PUT') @endif

                <div class="space-y-4">
                    <div>
                        <label for="name" class="form-label">Nama Level <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $jobLevel?->name) }}"
                            placeholder="Contoh: Staff, Supervisor, Manager"
                            class="form-input @error('name') border-red-400 @enderror">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="level" class="form-label">Urutan Level <span class="text-red-500">*</span></label>
                        <input id="level" name="level" type="number" min="1" max="99" required
                            value="{{ old('level', $jobLevel?->level) }}" placeholder="1 = tertinggi"
                            class="form-input @error('level') border-red-400 @enderror">
                        <p class="text-xs text-gray-400 mt-1">Angka lebih kecil = posisi lebih tinggi (misal: 1 = CEO,
                            10 = Staff)</p>
                        @error('level') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">
                        {{ $jobLevel ? 'Simpan Perubahan' : 'Tambah Level' }}
                    </button>
                    <a href="{{ route('settings.job-levels.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>