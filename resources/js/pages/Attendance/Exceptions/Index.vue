<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ApprovalActions from '@/components/ApprovalActions.vue';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type Exception = {
    id: number;
    work_date: string;
    exception_type: string;
    duration_hours: number | null;
    reason: string | null;
    approval_status: string;
    employee: { id: number; full_name: string };
    approved_by: { id: number; name: string } | null;
};

type PaginatedExceptions = { data: Exception[] } & PaginationMeta & { links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    exceptions: PaginatedExceptions;
    filters: { status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Pengecualian', href: '/attendance/exceptions' },
];

const status = ref(props.filters.status ?? '');

watch(status, () => {
    router.get('/attendance/exceptions', { status: status.value || undefined }, { preserveState: true, replace: true });
});

const EXCEPTION_LABELS: Record<string, string> = {
    leave: 'Cuti', overtime: 'Lembur', holiday: 'Libur', sick: 'Sakit', permit: 'Izin', half_day_permit: 'Izin ½ Hari',
};

const columns: ColumnDef<Exception, unknown>[] = [
    {
        id: 'employee',
        header: 'Karyawan',
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.employee.full_name),
    },
    { accessorKey: 'work_date', header: 'Tanggal' },
    {
        accessorKey: 'exception_type',
        header: 'Tipe',
        cell: ({ row }) => EXCEPTION_LABELS[row.original.exception_type] ?? row.original.exception_type,
    },
    {
        accessorKey: 'duration_hours',
        header: 'Durasi',
        cell: ({ row }) => row.original.duration_hours ? `${row.original.duration_hours}j` : '-',
    },
    {
        accessorKey: 'approval_status',
        header: 'Status',
        cell: ({ row }) => h(StatusBadge, { status: row.original.approval_status }),
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => h(ApprovalActions, {
            status: row.original.approval_status,
            approveUrl: `/attendance/exceptions/${row.original.id}/approve`,
            rejectUrl: `/attendance/exceptions/${row.original.id}/reject`,
        }),
    },
];
</script>

<template>
    <Head title="Pengecualian Absensi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <AlertTriangle class="h-5 w-5" /> Pengecualian Absensi
                        </CardTitle>
                        <CardDescription>{{ exceptions.total }} pengecualian</CardDescription>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="mb-4">
                        <Select v-model="status">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__clear__">Semua</SelectItem>
                                <SelectItem value="pending">Menunggu</SelectItem>
                                <SelectItem value="approved">Disetujui</SelectItem>
                                <SelectItem value="rejected">Ditolak</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DataTable :columns="columns" :data="exceptions.data" :pagination="exceptions" empty-message="Tidak ada pengecualian." />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
