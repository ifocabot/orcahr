<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckSquare } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ApprovalActions from '@/components/ApprovalActions.vue';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

type LeaveRequest = {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    status: string;
    manager_approval_status: string;
    reason: string | null;
    reject_reason: string | null;
    leave_type: { id: number; name: string };
    employee: { id: number; full_name: string; employee_code: string; manager?: { id: number; full_name: string } };
};

type PaginatedRequests = { data: LeaveRequest[] } & PaginationMeta & { links: { url: string | null; label: string; active: boolean }[] };
type Employee = { id: number; full_name: string; employee_code: string };

const props = defineProps<{
    requests: PaginatedRequests;
    employees: Employee[];
    filters: { status?: string; employee_id?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Approval Cuti', href: '/leave/requests/approval' },
];

const status = ref(props.filters.status ?? 'pending');
const selectedEmployee = ref(props.filters.employee_id ?? '');
const rejectReasons = ref<Record<number, string>>({});

watch([status, selectedEmployee], () => {
    router.get('/leave/requests/approval', {
        status: status.value || undefined,
        employee_id: selectedEmployee.value || undefined,
    }, { preserveState: true, replace: true });
});

function approve(id: number) {
    router.put(`/leave/requests/${id}/approve`, {}, { preserveState: true });
}

function reject(id: number) {
    const reason = rejectReasons.value[id];
    if (!reason?.trim()) {
        alert('Harap isi alasan penolakan.');
        return;
    }
    router.put(`/leave/requests/${id}/reject`, { reject_reason: reason }, { preserveState: true });
}

const columns: ColumnDef<LeaveRequest, unknown>[] = [
    {
        id: 'employee',
        header: 'Karyawan',
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium' }, row.original.employee.full_name),
            h('p', { class: 'text-muted-foreground text-xs font-mono' }, row.original.employee.employee_code),
        ]),
    },
    {
        accessorKey: 'leave_type',
        header: 'Jenis',
        cell: ({ row }) => row.original.leave_type.name,
    },
    {
        id: 'date_range',
        header: 'Tanggal',
        cell: ({ row }) => h('div', [
            h('p', { class: 'text-sm' }, `${row.original.start_date} → ${row.original.end_date}`),
            h('p', { class: 'text-muted-foreground text-xs' }, `${row.original.total_days} hari`),
        ]),
    },
    {
        accessorKey: 'reason',
        header: 'Alasan',
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.reason ?? '-'),
    },
    {
        id: 'manager_status',
        header: 'Manager',
        cell: ({ row }) => {
            if (!row.original.employee.manager) return h('span', { class: 'text-muted-foreground text-xs italic' }, 'N/A');
            return h(StatusBadge, { status: row.original.manager_approval_status });
        },
    },
    {
        accessorKey: 'status',
        header: 'HR/Final',
        cell: ({ row }) => h(StatusBadge, { status: row.original.status }),
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            if (row.original.status !== 'pending') return null;
            return h('div', { class: 'flex flex-col gap-2 min-w-[200px]' }, [
                h('div', { class: 'flex gap-2' }, [
                    h(Button, {
                        size: 'sm',
                        class: 'bg-green-600 hover:bg-green-700 text-white',
                        onClick: () => approve(row.original.id),
                    }, () => '✓ Setujui'),
                    h(Button, {
                        size: 'sm',
                        variant: 'destructive',
                        onClick: () => reject(row.original.id),
                    }, () => '✕ Tolak'),
                ]),
                h(Input, {
                    placeholder: 'Alasan penolakan...',
                    class: 'text-sm h-8',
                    value: rejectReasons.value[row.original.id] ?? '',
                    onInput: (e: Event) => {
                        rejectReasons.value[row.original.id] = (e.target as HTMLInputElement).value;
                    },
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Approval Cuti" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <CheckSquare class="h-5 w-5" /> Approval Cuti
                        </CardTitle>
                        <CardDescription>{{ requests.total }} pengajuan</CardDescription>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="mb-4 flex flex-wrap gap-3">
                        <Select v-model="status">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Menunggu</SelectItem>
                                <SelectItem value="approved">Disetujui</SelectItem>
                                <SelectItem value="rejected">Ditolak</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="selectedEmployee">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="Semua Karyawan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="emp in employees" :key="emp.id" :value="emp.id.toString()">
                                    {{ emp.full_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        :columns="columns"
                        :data="requests.data"
                        :pagination="requests"
                        empty-message="Tidak ada pengajuan cuti."
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
