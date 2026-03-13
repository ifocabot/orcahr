<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
    label?: string;
}>();

const STATUS_MAP: Record<string, { label: string; class: string }> = {
    // Employment
    active:     { label: 'Aktif',       class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' },
    probation:  { label: 'Probation',   class: 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' },
    resigned:   { label: 'Resign',      class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' },
    terminated: { label: 'Terminated',  class: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' },

    // Attendance
    present:      { label: 'Hadir',         class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' },
    absent:       { label: 'Tidak Hadir',   class: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' },
    late:         { label: 'Terlambat',     class: 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' },
    leave:        { label: 'Cuti',          class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200' },
    holiday:      { label: 'Libur',         class: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200' },
    half_permit:  { label: 'Izin ½ Hari',   class: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-200' },

    // Leave & Approval
    pending:   { label: 'Menunggu',   class: 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' },
    approved:  { label: 'Disetujui',  class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' },
    rejected:  { label: 'Ditolak',    class: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' },
    cancelled: { label: 'Dibatalkan', class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' },

    // Payroll
    draft:      { label: 'Draft',      class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' },
    calculated: { label: 'Dihitung',   class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200' },
    paid:       { label: 'Dibayar',    class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' },

    // Boolean-like
    inactive:  { label: 'Nonaktif', class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' },
};

const resolved = computed(() => {
    const key = props.status?.toLowerCase() ?? '';
    return STATUS_MAP[key] ?? {
        label: props.status,
        class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    };
});
</script>

<template>
    <Badge variant="secondary" :class="resolved.class">
        {{ label ?? resolved.label }}
    </Badge>
</template>
