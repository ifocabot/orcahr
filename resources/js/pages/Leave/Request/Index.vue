<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ClipboardList, Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/toast/use-toast';

type LeaveRequest = {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    status: string;
    reason: string | null;
    reject_reason: string | null;
    leave_type: { id: number; name: string; code: string };
    created_at: string;
};

type PaginatedRequests = { data: LeaveRequest[] } & PaginationMeta & { links: { url: string | null; label: string; active: boolean }[] };
type LeaveType = { id: number; name: string; code: string };

const props = defineProps<{
    requests: PaginatedRequests;
    leaveTypes: LeaveType[];
    filters: { status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Riwayat Cuti', href: '/leave/requests' },
];

const status = ref(props.filters.status ?? '');

watch(status, () => {
    router.get('/leave/requests', { status: status.value || undefined }, { preserveState: true, replace: true });
});

function cancelRequest(id: number) {
    if (!confirm('Batalkan pengajuan cuti ini?')) return;
    router.put(`/leave/requests/${id}/cancel`, {}, { preserveState: true });
}

const columns: ColumnDef<LeaveRequest, unknown>[] = [
    {
        accessorKey: 'leave_type',
        header: 'Jenis Cuti',
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium' }, row.original.leave_type.name),
            h('p', { class: 'text-muted-foreground text-xs font-mono' }, row.original.leave_type.code),
        ]),
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
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h('div', { class: 'space-y-1' }, [
            h(StatusBadge, { status: row.original.status }),
            row.original.reject_reason
                ? h('p', { class: 'text-muted-foreground text-xs' }, row.original.reject_reason)
                : null,
        ]),
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => row.original.status === 'pending'
            ? h(Button, {
                variant: 'outline',
                size: 'sm',
                class: 'text-destructive hover:text-destructive',
                onClick: () => cancelRequest(row.original.id),
            }, () => 'Batalkan')
            : null,
    },
];
</script>

<template>
    <Head title="Riwayat Cuti" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <ClipboardList class="h-5 w-5" /> Riwayat Cuti
                        </CardTitle>
                        <CardDescription>{{ requests.total }} pengajuan</CardDescription>
                    </div>
                    <Link href="/leave/requests/create">
                        <Button size="sm" class="gap-1">
                            <Plus class="h-4 w-4" /> Ajukan Cuti
                        </Button>
                    </Link>
                </CardHeader>

                <CardContent>
                    <div class="mb-4">
                        <Select v-model="status">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__all__">Semua</SelectItem>
                                <SelectItem value="pending">Menunggu</SelectItem>
                                <SelectItem value="approved">Disetujui</SelectItem>
                                <SelectItem value="rejected">Ditolak</SelectItem>
                                <SelectItem value="cancelled">Dibatalkan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DataTable :columns="columns" :data="requests.data" :pagination="requests" empty-message="Belum ada pengajuan cuti." />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
