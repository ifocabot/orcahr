<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDays, Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

type Schedule = {
    id: number;
    start_date: string;
    end_date: string | null;
    status: string;
    employee: { id: number; full_name: string; employee_code: string };
    shift: { id: number; name: string; start_time: string; end_time: string };
};

type PaginatedSchedules = { data: Schedule[] } & PaginationMeta & { links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    schedules: PaginatedSchedules;
    employees: { id: number; full_name: string; employee_code: string }[];
    filters: { employee_id?: string; month?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Jadwal', href: '/attendance/schedules' },
];

const employeeId = ref(props.filters.employee_id ?? '');
const month = ref(props.filters.month ?? '');

const applyFilters = () => {
    router.get('/attendance/schedules', {
        employee_id: employeeId.value || undefined,
        month: month.value || undefined,
    }, { preserveState: true, replace: true });
};

watch([employeeId, month], applyFilters);

const columns: ColumnDef<Schedule, unknown>[] = [
    {
        id: 'employee',
        header: 'Karyawan',
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium' }, row.original.employee.full_name),
            h('p', { class: 'text-xs text-muted-foreground font-mono' }, row.original.employee.employee_code),
        ]),
    },
    {
        id: 'shift',
        header: 'Shift',
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium' }, row.original.shift.name),
            h('p', { class: 'text-xs text-muted-foreground font-mono' },
                `${row.original.shift.start_time.slice(0,5)} – ${row.original.shift.end_time.slice(0,5)}`),
        ]),
    },
    { accessorKey: 'start_date', header: 'Mulai' },
    {
        accessorKey: 'end_date',
        header: 'Selesai',
        cell: ({ row }) => row.original.end_date ?? 'Open-ended',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h(StatusBadge, { status: row.original.status }),
    },
];
</script>

<template>
    <Head title="Jadwal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <CalendarDays class="h-5 w-5" /> Jadwal
                        </CardTitle>
                        <CardDescription>Total {{ schedules.total }} jadwal aktif</CardDescription>
                    </div>
                    <Link href="/attendance/schedules/generate">
                        <Button><Plus class="mr-2 h-4 w-4" /> Generate Jadwal</Button>
                    </Link>
                </CardHeader>

                <CardContent>
                    <div class="mb-4 flex flex-wrap gap-3">
                        <Select v-model="employeeId">
                            <SelectTrigger class="w-[220px]">
                                <SelectValue placeholder="Semua Karyawan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__clear__">Semua Karyawan</SelectItem>
                                <SelectItem v-for="e in employees" :key="e.id" :value="String(e.id)">
                                    {{ e.full_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-model="month" type="month" class="w-[160px]" />
                    </div>

                    <DataTable :columns="columns" :data="schedules.data" :pagination="schedules" empty-message="Belum ada jadwal." />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
