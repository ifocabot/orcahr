<script setup lang="ts">
import { h, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Plus, Search, Eye, Pencil } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import type { ColumnDef } from '@tanstack/vue-table';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable, { type PaginationMeta } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { index as employeesIndex, create as employeesCreate, show as employeesShow, edit as employeesEdit } from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type Employee = {
    id: number;
    employee_code: string;
    full_name: string;
    email: string;
    employment_status: string;
    join_date: string;
    department?: { id: number; name: string };
    position?: { id: number; name: string };
    job_level?: { id: number; name: string };
};

type PaginatedData = {
    data: Employee[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    employees: PaginatedData;
    departments: { id: number; name: string }[];
    filters: {
        search?: string;
        department_id?: string;
        status?: string;
        per_page?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Karyawan', href: employeesIndex.url() },
];

const search = ref(props.filters.search ?? '');
const departmentId = ref(props.filters.department_id ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = useDebounceFn(() => {
    router.get(employeesIndex.url(), {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}, 300);

watch([search], applyFilters);
watch([departmentId, status], () => {
    if (departmentId.value === '__clear__') departmentId.value = '';
    if (status.value === '__clear__') status.value = '';
    router.get(employeesIndex.url(), {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
});

const columns: ColumnDef<Employee, unknown>[] = [
    {
        accessorKey: 'employee_code',
        header: 'Kode',
        cell: ({ row }) => h('span', { class: 'font-mono text-xs' }, row.original.employee_code),
    },
    {
        accessorKey: 'full_name',
        header: 'Nama',
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.full_name),
    },
    {
        id: 'department',
        header: 'Departemen',
        cell: ({ row }) => row.original.department?.name ?? '-',
    },
    {
        id: 'position',
        header: 'Jabatan',
        cell: ({ row }) => row.original.position?.name ?? '-',
    },
    {
        accessorKey: 'employment_status',
        header: 'Status',
        cell: ({ row }) => h(StatusBadge, { status: row.original.employment_status }),
    },
    {
        accessorKey: 'join_date',
        header: 'Tgl. Masuk',
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => h('div', { class: 'flex gap-1' }, [
            h(Link, { href: employeesShow.url(row.original.id) }, () =>
                h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () =>
                    h(Eye, { class: 'h-4 w-4' }),
                ),
            ),
            h(Link, { href: employeesEdit.url(row.original.id) }, () =>
                h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () =>
                    h(Pencil, { class: 'h-4 w-4' }),
                ),
            ),
        ]),
        meta: { class: 'w-[100px]' },
    },
];

const pagination: PaginationMeta = {
    current_page: props.employees.current_page,
    last_page: props.employees.last_page,
    per_page: props.employees.per_page,
    total: props.employees.total,
    links: props.employees.links,
};
</script>

<template>
    <Head title="Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Users class="h-5 w-5" /> Karyawan
                        </CardTitle>
                        <CardDescription>Total {{ employees.total }} karyawan terdaftar</CardDescription>
                    </div>
                    <Link :href="employeesCreate.url()">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" /> Tambah Karyawan
                        </Button>
                    </Link>
                </CardHeader>

                <CardContent>
                    <!-- Filters -->
                    <div class="mb-4 flex flex-wrap gap-3">
                        <div class="relative w-full max-w-xs">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari nama / kode..." class="pl-9" />
                        </div>
                        <Select v-model="departmentId">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Departemen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__clear__">Semua</SelectItem>
                                <SelectItem v-for="d in departments" :key="d.id" :value="String(d.id)">
                                    {{ d.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="status">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__clear__">Semua</SelectItem>
                                <SelectItem value="active">Aktif</SelectItem>
                                <SelectItem value="probation">Probation</SelectItem>
                                <SelectItem value="resigned">Resign</SelectItem>
                                <SelectItem value="terminated">Terminated</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <DataTable
                        :columns="columns"
                        :data="employees.data"
                        :pagination="pagination"
                        empty-message="Belum ada data karyawan"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
