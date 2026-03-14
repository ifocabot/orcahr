<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as employeesIndex, update as employeesUpdate } from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    employee: {
        id: number;
        employee_code: string;
        full_name: string;
        email: string;
        nik: string;
        npwp: string;
        phone: string;
        bank_name: string;
        bank_account_number: string;
        bank_account_name: string;
        department_id: number | null;
        position_id: number | null;
        job_level_id: number | null;
        join_date: string;
        resign_date: string | null;
        employment_status: string;
        gender: string | null;
        manager_id: number | null;
    };
    departments: { id: number; name: string }[];
    positions: { id: number; name: string; department_id: number }[];
    jobLevels: { id: number; name: string }[];
    managers: { id: number; full_name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Karyawan', href: employeesIndex.url() },
    { title: 'Edit', href: '#' },
];

const form = useForm({
    employee_code: props.employee.employee_code,
    full_name: props.employee.full_name,
    email: props.employee.email,
    nik: props.employee.nik ?? '',
    npwp: props.employee.npwp ?? '',
    phone: props.employee.phone ?? '',
    bank_name: props.employee.bank_name ?? '',
    bank_account_number: props.employee.bank_account_number ?? '',
    bank_account_name: props.employee.bank_account_name ?? '',
    department_id: props.employee.department_id ? String(props.employee.department_id) : '',
    position_id: props.employee.position_id ? String(props.employee.position_id) : '',
    job_level_id: props.employee.job_level_id ? String(props.employee.job_level_id) : '',
    join_date: props.employee.join_date,
    resign_date: props.employee.resign_date ?? '',
    employment_status: props.employee.employment_status,
    gender: props.employee.gender ?? '',
    manager_id: props.employee.manager_id ? String(props.employee.manager_id) : '',
});

const submit = () => {
    form.put(employeesUpdate.url(props.employee.id));
};
</script>

<template>
    <Head :title="`Edit: ${employee.full_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Link :href="employeesIndex.url()">
                    <Button variant="outline" size="icon"><ArrowLeft class="h-4 w-4" /></Button>
                </Link>
                <h1 class="text-2xl font-bold">Edit: {{ employee.full_name }}</h1>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-2">
                <Card class="md:col-span-2">
                    <CardHeader><CardTitle>Informasi Dasar</CardTitle></CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="employee_code">Kode Karyawan *</Label>
                            <Input id="employee_code" v-model="form.employee_code" />
                            <p v-if="form.errors.employee_code" class="text-sm text-destructive">{{ form.errors.employee_code }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="full_name">Nama Lengkap *</Label>
                            <Input id="full_name" v-model="form.full_name" />
                            <p v-if="form.errors.full_name" class="text-sm text-destructive">{{ form.errors.full_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="email">Email *</Label>
                            <Input id="email" type="email" v-model="form.email" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">No. Telepon</Label>
                            <Input id="phone" v-model="form.phone" />
                        </div>
                        <div class="space-y-2">
                            <Label>Gender</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger><SelectValue placeholder="Pilih" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Laki-laki</SelectItem>
                                    <SelectItem value="female">Perempuan</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Status *</Label>
                            <Select v-model="form.employment_status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="probation">Probation</SelectItem>
                                    <SelectItem value="resigned">Resign</SelectItem>
                                    <SelectItem value="terminated">Terminated</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Data Identitas 🔒</CardTitle></CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="space-y-2">
                            <Label for="nik">NIK</Label>
                            <Input id="nik" v-model="form.nik" maxlength="16" />
                        </div>
                        <div class="space-y-2">
                            <Label for="npwp">NPWP</Label>
                            <Input id="npwp" v-model="form.npwp" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Data Bank 🔒</CardTitle></CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="space-y-2">
                            <Label>Nama Bank</Label>
                            <Input v-model="form.bank_name" />
                        </div>
                        <div class="space-y-2">
                            <Label>No. Rekening</Label>
                            <Input v-model="form.bank_account_number" />
                        </div>
                        <div class="space-y-2">
                            <Label>Nama Pemilik Rekening</Label>
                            <Input v-model="form.bank_account_name" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="md:col-span-2">
                    <CardHeader><CardTitle>Organisasi</CardTitle></CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <Label>Departemen</Label>
                            <Select v-model="form.department_id">
                                <SelectTrigger><SelectValue placeholder="Pilih Departemen" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Jabatan</Label>
                            <Select v-model="form.position_id">
                                <SelectTrigger><SelectValue placeholder="Pilih Jabatan" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in positions" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Level</Label>
                            <Select v-model="form.job_level_id">
                                <SelectTrigger><SelectValue placeholder="Pilih Level" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="l in jobLevels" :key="l.id" :value="String(l.id)">{{ l.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="join_date">Tanggal Masuk *</Label>
                            <Input id="join_date" type="date" v-model="form.join_date" />
                        </div>
                        <div class="space-y-2">
                            <Label for="resign_date">Tanggal Resign</Label>
                            <Input id="resign_date" type="date" v-model="form.resign_date" />
                        </div>
                        <div class="space-y-2">
                            <Label>Atasan (Manager)</Label>
                            <Select v-model="form.manager_id">
                                <SelectTrigger><SelectValue placeholder="Pilih Manager" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">— Tanpa Atasan</SelectItem>
                                    <SelectItem v-for="m in managers" :key="m.id" :value="String(m.id)">{{ m.full_name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <Link :href="employeesIndex.url()">
                        <Button variant="outline" type="button">Batal</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Update Karyawan' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
