{{-- Form fields partial (dipakai di create & edit) --}}

<div>
    <label class="form-label">Nama Shift <span class="text-red-500">*</span></label>
    <input name="name" type="text" required x-model="name" placeholder="Contoh: WFO Pagi, WFH, Flexible"
        class="form-input @error('name') border-red-400 @enderror">
    @error('name') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div>
    <label class="form-label">Kode <span class="text-red-500">*</span></label>
    <input name="code" type="text" required x-model="code" placeholder="Contoh: WFO, FLEX, SHIFT-A"
        class="form-input uppercase @error('code') border-red-400 @enderror">
    <p class="text-xs text-gray-400 mt-1">Kode unik, huruf besar</p>
    @error('code') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="flex items-center gap-3">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="hidden" name="is_flexible" value="0">
        <input type="checkbox" name="is_flexible" value="1" x-model="is_flexible"
            class="w-4 h-4 rounded accent-brand-600">
        <span class="text-sm font-medium text-gray-700">Flexible (tidak ada batas jam masuk)</span>
    </label>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="form-label">Jam Masuk <span class="text-red-500">*</span></label>
        <input name="clock_in" type="time" required x-model="clock_in"
            class="form-input @error('clock_in') border-red-400 @enderror">
        @error('clock_in') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="form-label">Jam Pulang <span class="text-red-500">*</span></label>
        <input name="clock_out" type="time" required x-model="clock_out"
            class="form-input @error('clock_out') border-red-400 @enderror">
        @error('clock_out') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="form-label">Mulai Istirahat</label>
        <input name="break_start" type="time" x-model="break_start" class="form-input">
    </div>
    <div>
        <label class="form-label">Selesai Istirahat</label>
        <input name="break_end" type="time" x-model="break_end" class="form-input">
    </div>
</div>

<template x-if="!is_flexible">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="form-label">Toleransi Terlambat (mnt)</label>
            <input name="late_tolerance_minutes" type="number" min="0" max="120" x-model="late_tolerance_minutes"
                class="form-input">
        </div>
        <div>
            <label class="form-label">Toleransi Pulang Awal (mnt)</label>
            <input name="early_leave_tolerance_minutes" type="number" min="0" max="120"
                x-model="early_leave_tolerance_minutes" class="form-input">
        </div>
    </div>
</template>

<div>
    <label class="form-label">Keterangan <span class="text-xs text-gray-400 font-normal">(opsional)</span></label>
    <input name="description" type="text" x-model="description" placeholder="Mis: Shift kantor hari Senin–Jumat"
        maxlength="255" class="form-input">
</div>