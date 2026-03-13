<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ClipboardList, Download } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';

type Summary = {
    id: number;
    work_date: string;
    status: string;
    late_minutes: number;
    overtime_minutes: number;
    work_duration_minutes: number;
    actual_in: string | null;
    actual_out: string | null;
    employee: { full_name: string; employee_code: string; department?: { name: string } };
    shift: { name: string } | null;
};

type Paginated = { data: Summary[] } & PaginationMeta & { links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    summaries: Paginated;
    departments: { id: number; name: string }[];
    filters: { month?: string; department_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Timesheet', href: '/attendance/timesheet' },
];

const month = ref(props.filters.month ?? new Date().toISOString().slice(0, 7));
const departmentId = ref(props.filters.department_id ?? '');

const applyFilters = () => {
    router.get('/attendance/timesheet', {
        month: month.value || undefined,
        department_id: departmentId.value || undefined,
    }, { preserveState: true, replace: true });
};

watch([month, departmentId], applyFilters);

const exportUrl = () => {
    const params = new URLSearchParams();
    if (month.value) params.set('month', month.value);
    if (departmentId.value) params.set('department_id', departmentId.value);
    return `/attendance/timesheet/export?${params.toString()}`;
};

const fmt = (t: string | null) => t ? new Date(t).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';

const columns: ColumnDef<Summary, unknown>[] = [
    {
        id: 'employee',
        header: 'Karyawan',
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium text-sm' }, row.original.employee.full_name),
            h('p', { class: 'text-xs text-muted-foreground' }, row.original.employee.department?.name ?? '-'),
        ]),
    },
    { accessorKey: 'work_date', header: 'Tanggal' },
    {
        id: 'shift',
        header: 'Shift',
        cell: ({ row }) => row.original.shift?.name ?? '-',
    },
    {
        id: 'clock_in',
        header: 'Masuk',
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, fmt(row.original.actual_in)),
    },
    {
        id: 'clock_out',
        header: 'Pulang',
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, fmt(row.original.actual_out)),
    },
    {
        id: 'duration',
        header: 'Durasi',
        cell: ({ row }) => {
            const d = row.original.work_duration_minutes;
            return `${Math.floor(d / 60)}j ${d % 60}m`;
        },
    },
    {
        accessorKey: 'late_minutes',
        header: 'Terlambat',
        cell: ({ row }) => row.original.late_minutes ? `${row.original.late_minutes} mnt` : '-',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h(StatusBadge, { status: row.original.status }),
    },
];
</script>

<template>
    <Head title="Timesheet" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <ClipboardList class="h-5 w-5" /> Timesheet
                        </CardTitle>
                        <CardDescription>{{ summaries.total }} records</CardDescription>
                    </div>
                    <a :href="exportUrl()">
                        <Button variant="outline" class="flex items-center gap-2">
                            <Download class="h-4 w-4" /> Export Excel
                        </Button>
                    </a>
                </CardHeader>

                <CardContent>
                    <div class="mb-4 flex flex-wrap gap-3">
                        <Input v-model="month" type="month" class="w-[160px]" />
                        <Select v-model="departmentId">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="Semua Departemen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__clear__">Semua Departemen</SelectItem>
                                <SelectItem v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DataTable :columns="columns" :data="summaries.data" :pagination="summaries" empty-message="Tidak ada data timesheet." />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
