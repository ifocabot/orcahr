<x-layouts.app :title="'Tambah Karyawan'">

    <div class="page-header">
        <div>
            <h2 class="page-title">Tambah Karyawan</h2>
            <p class="page-subtitle">Isi data karyawan baru</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    {{-- Tab Form --}}
    <div x-data="{
        tab: 'personal',
        tabs: ['personal', 'employment', 'bank', 'bpjs'],
        labels: { personal: 'Info Pribadi', employment: 'Info Pekerjaan', bank: 'Rekening Bank', bpjs: 'BPJS' },
        isDone: { personal: false, employment: false, bank: true, bpjs: true },
        next() { const i = this.tabs.indexOf(this.tab); if (i < this.tabs.length - 1) this.tab = this.tabs[i+1]; },
        prev() { const i = this.tabs.indexOf(this.tab); if (i > 0) this.tab = this.tabs[i-1]; }
    }" class="space-y-0">

        {{-- Tab Headers --}}
        <div class="card-flat overflow-hidden mb-0">
            <div class="flex border-b border-gray-100">
                <template x-for="t in tabs" :key="t">
                    <button type="button" @click="tab = t" :class="tab === t
                                ? 'border-b-2 border-brand-600 text-brand-700 bg-brand-50/50'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="px-5 py-3.5 text-sm font-medium transition-colors flex items-center gap-2">
                        <span x-text="labels[t]"></span>
                    </button>
                </template>
            </div>
        </div>

        <form method="POST" action="{{ route('employees.store') }}" class="space-y-0">
            @csrf

            {{-- Tab: Info Pribadi --}}
            <div x-show="tab === 'personal'" class="card">
                @if($errors->hasAny(['full_name', 'email', 'gender', 'birth_date', 'nik', 'npwp', 'phone', 'personal_email', 'address']))
                    <div class="mb-4 p-3 bg-red-50 rounded-lg text-sm text-red-700 border border-red-100">
                        Ada kesalahan pada tab Info Pribadi. Silakan periksa kembali.
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input name="full_name" type="text" required value="{{ old('full_name') }}"
                            class="form-input @error('full_name') border-red-400 @enderror">
                        @error('full_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Email Kantor <span class="text-red-500">*</span></label>
                        <input name="email" type="email" required value="{{ old('email') }}"
                            class="form-input @error('email') border-red-400 @enderror">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-input">
                            <option value="">— Pilih —</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status Pernikahan</label>
                        <select name="marital_status" class="form-input">
                            <option value="">— Pilih —</option>
                            @foreach(['single' => 'Belum Menikah', 'married' => 'Menikah', 'divorced' => 'Cerai Hidup', 'widowed' => 'Cerai Mati'] as $val => $label)
                                <option value="{{ $val }}" {{ old('marital_status') === $val ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Tempat Lahir</label>
                        <input name="birth_place" type="text" value="{{ old('birth_place') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input name="birth_date" type="date" value="{{ old('birth_date') }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Agama</label>
                        <select name="religion" class="form-input">
                            <option value="">— Pilih —</option>
                            @foreach(['islam' => 'Islam', 'kristen' => 'Kristen Protestan', 'katolik' => 'Katolik', 'hindu' => 'Hindu', 'buddha' => 'Buddha', 'konghucu' => 'Konghucu', 'other' => 'Lainnya'] as $val => $label)
                                <option value="{{ $val }}" {{ old('religion') === $val ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Golongan Darah</label>
                        <select name="blood_type" class="form-input">
                            <option value="">— Pilih —</option>
                            @foreach(['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2">
                        <hr class="my-1 border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide my-3">Data Sensitif
                            (Terenkripsi)</p>
                    </div>

                    <div>
                        <label class="form-label">NIK (KTP)</label>
                        <input name="nik" type="text" value="{{ old('nik') }}" maxlength="20" placeholder="16 digit NIK"
                            class="form-input @error('nik') border-red-400 @enderror">
                        @error('nik') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">NPWP</label>
                        <input name="npwp" type="text" value="{{ old('npwp') }}" maxlength="20"
                            class="form-input @error('npwp') border-red-400 @enderror">
                        @error('npwp') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">No. HP</label>
                        <input name="phone" type="tel" value="{{ old('phone') }}" maxlength="20" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email Pribadi</label>
                        <input name="personal_email" type="email" value="{{ old('personal_email') }}"
                            class="form-input">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" rows="2" class="form-input">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end mt-5">
                    <button type="button" @click="next()" class="btn btn-primary">Lanjut →</button>
                </div>
            </div>

            {{-- Tab: Info Pekerjaan --}}
            <div x-show="tab === 'employment'" class="card">
                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" required
                            class="form-input @error('department_id') border-red-400 @enderror">
                            <option value="">— Pilih Department —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') === $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Posisi <span class="text-red-500">*</span></label>
                        <select name="position_id" required
                            class="form-input @error('position_id') border-red-400 @enderror">
                            <option value="">— Pilih Posisi —</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id') === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('position_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Job Level <span class="text-red-500">*</span></label>
                        <select name="job_level_id" required
                            class="form-input @error('job_level_id') border-red-400 @enderror">
                            <option value="">— Pilih Level —</option>
                            @foreach($jobLevels as $l)
                                <option value="{{ $l->id }}" {{ old('job_level_id') === $l->id ? 'selected' : '' }}>
                                    {{ $l->name }} (Lv. {{ $l->level }})</option>
                            @endforeach
                        </select>
                        @error('job_level_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Status Kepegawaian <span class="text-red-500">*</span></label>
                        <select name="employment_status" required class="form-input">
                            <option value="permanent" {{ old('employment_status', 'permanent') === 'permanent' ? 'selected' : '' }}>Tetap (Permanent)</option>
                            <option value="contract" {{ old('employment_status') === 'contract' ? 'selected' : '' }}>
                                Kontrak</option>
                            <option value="probation" {{ old('employment_status') === 'probation' ? 'selected' : '' }}>
                                Masa Percobaan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Masuk <span class="text-red-500">*</span></label>
                        <input name="join_date" type="date" required value="{{ old('join_date') }}"
                            class="form-input @error('join_date') border-red-400 @enderror">
                        @error('join_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal Berakhir</label>
                        <input name="end_date" type="date" value="{{ old('end_date') }}" class="form-input">
                        <p class="text-xs text-gray-400 mt-1">Kosongkan jika permanent / belum ditentukan</p>
                    </div>
                </div>
                <div class="flex justify-between mt-5">
                    <button type="button" @click="prev()" class="btn btn-secondary">← Kembali</button>
                    <button type="button" @click="next()" class="btn btn-primary">Lanjut →</button>
                </div>
            </div>

            {{-- Tab: Rekening Bank --}}
            <div x-show="tab === 'bank'" class="card">
                <p class="text-sm text-gray-500 mb-4">Opsional — bisa diisi belakangan melalui halaman detail karyawan.
                </p>
                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Nama Bank</label>
                        <input name="bank_name" type="text" value="{{ old('bank_name') }}"
                            placeholder="BCA, BRI, Mandiri..." class="form-input">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">Cabang</label>
                        <input name="branch" type="text" value="{{ old('branch') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">No. Rekening</label>
                        <input name="account_number" type="text" value="{{ old('account_number') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Atas Nama</label>
                        <input name="account_holder" type="text" value="{{ old('account_holder') }}" class="form-input">
                    </div>
                </div>
                <div class="flex justify-between mt-5">
                    <button type="button" @click="prev()" class="btn btn-secondary">← Kembali</button>
                    <button type="button" @click="next()" class="btn btn-primary">Lanjut →</button>
                </div>
            </div>

            {{-- Tab: BPJS --}}
            <div x-show="tab === 'bpjs'" class="card">
                <p class="text-sm text-gray-500 mb-4">Opsional — bisa diisi belakangan.</p>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">No. BPJS Kesehatan</label>
                        <input name="bpjs_kes" type="text" value="{{ old('bpjs_kes') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Kelas BPJS</label>
                        <select name="bpjs_class" class="form-input">
                            <option value="">— Pilih —</option>
                            @foreach(['1' => 'Kelas 1', '2' => 'Kelas 2', '3' => 'Kelas 3'] as $v => $l)
                                <option value="{{ $v }}" {{ old('bpjs_class') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="form-label">No. BPJS Ketenagakerjaan</label>
                        <input name="bpjs_tk" type="text" value="{{ old('bpjs_tk') }}" class="form-input">
                    </div>
                </div>
                <div class="flex justify-between mt-5">
                    <button type="button" @click="prev()" class="btn btn-secondary">← Kembali</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Karyawan
                    </button>
                </div>
            </div>

        </form>
    </div>

</x-layouts.app>