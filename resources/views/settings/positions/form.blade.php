<x-layouts.app :title="$position ? 'Edit Posisi' : 'Tambah Posisi'">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $position ? 'Edit Posisi' : 'Tambah Posisi' }}</h2>
        </div>
        <a href="{{ route('settings.positions.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div class="card max-w-lg">
        <div class="card-body">
            <form method="POST"
                  action="{{ $position ? route('settings.positions.update', $position) : route('settings.positions.store') }}">
                @csrf
                @if($position) @method('PUT') @endif

                <div class="space-y-4">
                    <div>
                        <label for="name" class="form-label">Nama Posisi <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" required
                               value="{{ old('name', $position?->name) }}"
                               placeholder="Contoh: HR Manager, Software Engineer"
                               class="form-input @error('name') border-red-400 @enderror">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="department_id" class="form-label">Department <span class="text-red-500">*</span></label>
                        <select id="department_id" name="department_id" required
                                class="form-input @error('department_id') border-red-400 @enderror">
                            <option value="">— Pilih Department —</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ old('department_id', $position?->department_id) === $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="job_level_id" class="form-label">Job Level <span class="text-red-500">*</span></label>
                        <select id="job_level_id" name="job_level_id" required
                                class="form-input @error('job_level_id') border-red-400 @enderror">
                            <option value="">— Pilih Level —</option>
                            @foreach($jobLevels as $level)
                            <option value="{{ $level->id }}"
                                {{ old('job_level_id', $position?->job_level_id) === $level->id ? 'selected' : '' }}>
                                {{ $level->name }} (Level {{ $level->level }})
                            </option>
                            @endforeach
                        </select>
                        @error('job_level_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">
                        {{ $position ? 'Simpan Perubahan' : 'Tambah Posisi' }}
                    </button>
                    <a href="{{ route('settings.positions.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
