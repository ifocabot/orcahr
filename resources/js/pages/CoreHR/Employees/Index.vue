<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Plus, Search, Eye, Pencil } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as employeesIndex, create as employeesCreate, show as employeesShow, edit as employeesEdit } from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

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

const statusColor = (s: string) => {
    const map: Record<string, string> = {
        active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
        probation: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        resigned: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        terminated: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return map[s] ?? '';
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
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Departemen</TableHead>
                                    <TableHead>Jabatan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Tgl. Masuk</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="emp in employees.data" :key="emp.id" class="hover:bg-muted/50">
                                    <TableCell class="font-mono text-xs">{{ emp.employee_code }}</TableCell>
                                    <TableCell class="font-medium">{{ emp.full_name }}</TableCell>
                                    <TableCell>{{ emp.department?.name ?? '-' }}</TableCell>
                                    <TableCell>{{ emp.position?.name ?? '-' }}</TableCell>
                                    <TableCell>
                                        <Badge :class="statusColor(emp.employment_status)" variant="secondary">
                                            {{ emp.employment_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ emp.join_date }}</TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Link :href="employeesShow.url(emp.id)">
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="employeesEdit.url(emp.id)">
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="employees.data.length === 0">
                                    <TableCell col-span="7" class="text-center py-8 text-muted-foreground">
                                        Belum ada data karyawan
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex items-center justify-between text-sm text-muted-foreground">
                        <span>Halaman {{ employees.current_page }} dari {{ employees.last_page }} ({{ employees.total }} data)</span>
                        <div class="flex gap-1">
                            <template v-for="link in employees.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 rounded text-sm border"
                                    :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                                    v-html="link.label"
                                    preserve-state
                                />
                                <span v-else class="px-3 py-1 rounded text-sm border opacity-50" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
