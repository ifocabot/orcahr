<x-layouts.app :title="$department ? 'Edit Department' : 'Tambah Department'">
    <div class="page-header">
        <div>
            <h2 class="page-title">{{ $department ? 'Edit Department' : 'Tambah Department' }}</h2>
        </div>
        <a href="{{ route('settings.departments.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div class="card max-w-lg">
        <div class="card-body">
            <form method="POST"
                action="{{ $department ? route('settings.departments.update', $department) : route('settings.departments.store') }}">
                @csrf
                @if($department) @method('PUT') @endif

                <div class="space-y-4">
                    <div>
                        <label for="name" class="form-label">Nama Department <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $department?->name) }}"
                            placeholder="Contoh: Human Resources, Finance"
                            class="form-input @error('name') border-red-400 @enderror">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="code" class="form-label">Kode <span class="text-red-500">*</span></label>
                        <input id="code" name="code" type="text" required value="{{ old('code', $department?->code) }}"
                            placeholder="HR, FIN, IT" class="form-input @error('code') border-red-400 @enderror">
                        <p class="text-xs text-gray-400 mt-1">Kode unik, hanya huruf, angka, dan underscore</p>
                        @error('code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="parent_id" class="form-label">Parent Department</label>
                        <select id="parent_id" name="parent_id"
                            class="form-input @error('parent_id') border-red-400 @enderror">
                            <option value="">— Tidak ada (top-level) —</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $department?->parent_id) === $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }} ({{ $parent->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">
                        {{ $department ? 'Simpan Perubahan' : 'Tambah Department' }}
                    </button>
                    <a href="{{ route('settings.departments.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>