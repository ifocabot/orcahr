<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as employeesIndex, edit as employeesEdit } from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil, Shield, Settings2, Coins } from 'lucide-vue-next';

const props = defineProps<{
    employee: {
        id: number;
        employee_code: string;
        full_name: string;
        email: string;
        nik: string | null;
        npwp: string | null;
        phone: string | null;
        bank_name: string | null;
        bank_account_number: string | null;
        bank_account_name: string | null;
        join_date: string;
        resign_date: string | null;
        employment_status: string;
        gender: string | null;
        department?: { id: number; name: string };
        position?: { id: number; name: string };
        job_level?: { id: number; name: string };
        user?: { id: number; name: string; email: string };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Karyawan', href: employeesIndex.url() },
    { title: props.employee.full_name, href: '#' },
];

const mask = (val: string | null) => val ? val.slice(0, 4) + '****' : '-';
const statusLabel: Record<string, string> = {
    active: 'Aktif', probation: 'Probation', resigned: 'Resign', terminated: 'Terminated',
};
</script>

<template>
    <Head :title="employee.full_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="employeesIndex.url()">
                        <Button variant="outline" size="icon"><ArrowLeft class="h-4 w-4" /></Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold">{{ employee.full_name }}</h1>
                        <p class="text-sm text-muted-foreground font-mono">{{ employee.employee_code }}</p>
                    </div>
                </div>
                <Link :href="employeesEdit.url(employee.id)">
                    <Button><Pencil class="mr-2 h-4 w-4" /> Edit</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle>Informasi Dasar</CardTitle></CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <Badge variant="secondary">{{ statusLabel[employee.employment_status] }}</Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Email</span>
                            <span>{{ employee.email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Telepon</span>
                            <span>{{ mask(employee.phone) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Gender</span>
                            <span>{{ employee.gender === 'male' ? 'Laki-laki' : employee.gender === 'female' ? 'Perempuan' : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Tgl. Masuk</span>
                            <span>{{ employee.join_date }}</span>
                        </div>
                        <div v-if="employee.resign_date" class="flex justify-between">
                            <span class="text-muted-foreground">Tgl. Resign</span>
                            <span>{{ employee.resign_date }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Organisasi</CardTitle></CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Departemen</span>
                            <span>{{ employee.department?.name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Jabatan</span>
                            <span>{{ employee.position?.name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Level</span>
                            <span>{{ employee.job_level?.name ?? '-' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-4 w-4" /> Data Identitas
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">NIK</span>
                            <span class="font-mono">{{ mask(employee.nik) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">NPWP</span>
                            <span class="font-mono">{{ mask(employee.npwp) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-4 w-4" /> Data Bank
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Bank</span>
                            <span>{{ employee.bank_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">No. Rekening</span>
                            <span class="font-mono">{{ mask(employee.bank_account_number) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Atas Nama</span>
                            <span>{{ mask(employee.bank_account_name) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- HR Actions -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Aksi HR</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-wrap gap-3">
                    <Link :href="`/employees/${employee.id}/payroll-configs`">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-muted">
                            <Coins class="h-4 w-4 text-primary" /> Konfigurasi Gaji
                        </button>
                    </Link>
                    <Link :href="`/employees/${employee.id}/edit`">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-muted">
                            <Settings2 class="h-4 w-4" /> Edit Data Karyawan
                        </button>
                    </Link>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>
