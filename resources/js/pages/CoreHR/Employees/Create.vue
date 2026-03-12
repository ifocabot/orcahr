<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as employeesIndex, store as employeesStore } from '@/routes/employees';
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
    departments: { id: number; name: string }[];
    positions: { id: number; name: string; department_id: number }[];
    jobLevels: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Karyawan', href: employeesIndex.url() },
    { title: 'Tambah', href: '#' },
];

const form = useForm({
    employee_code: '',
    full_name: '',
    email: '',
    nik: '',
    npwp: '',
    phone: '',
    bank_name: '',
    bank_account_number: '',
    bank_account_name: '',
    department_id: '',
    position_id: '',
    job_level_id: '',
    join_date: '',
    employment_status: 'active',
    gender: '',
});

const submit = () => {
    form.post(employeesStore.url());
};
</script>

<template>
    <Head title="Tambah Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Link :href="employeesIndex.url()">
                    <Button variant="outline" size="icon"><ArrowLeft class="h-4 w-4" /></Button>
                </Link>
                <h1 class="text-2xl font-bold">Tambah Karyawan</h1>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-2">
                <!-- Informasi Dasar -->
                <Card class="md:col-span-2">
                    <CardHeader><CardTitle>Informasi Dasar</CardTitle></CardHeader>
                    <CardContent class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="employee_code">Kode Karyawan *</Label>
                            <Input id="employee_code" v-model="form.employee_code" placeholder="EMP-20260101-001" />
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
                            <Input id="phone" v-model="form.phone" placeholder="+62..." />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">Gender</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger><SelectValue placeholder="Pilih" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Laki-laki</SelectItem>
                                    <SelectItem value="female">Perempuan</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="employment_status">Status *</Label>
                            <Select v-model="form.employment_status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="probation">Probation</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <!-- Data Sensitif (Encrypted) -->
                <Card>
                    <CardHeader><CardTitle>Data Identitas 🔒</CardTitle></CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="space-y-2">
                            <Label for="nik">NIK</Label>
                            <Input id="nik" v-model="form.nik" placeholder="16 digit" maxlength="16" />
                        </div>
                        <div class="space-y-2">
                            <Label for="npwp">NPWP</Label>
                            <Input id="npwp" v-model="form.npwp" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Data Bank (Encrypted) -->
                <Card>
                    <CardHeader><CardTitle>Data Bank 🔒</CardTitle></CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="space-y-2">
                            <Label for="bank_name">Nama Bank</Label>
                            <Input id="bank_name" v-model="form.bank_name" placeholder="BCA, BRI, Mandiri..." />
                        </div>
                        <div class="space-y-2">
                            <Label for="bank_account_number">No. Rekening</Label>
                            <Input id="bank_account_number" v-model="form.bank_account_number" />
                        </div>
                        <div class="space-y-2">
                            <Label for="bank_account_name">Nama Pemilik Rekening</Label>
                            <Input id="bank_account_name" v-model="form.bank_account_name" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Organisasi -->
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
                            <p v-if="form.errors.join_date" class="text-sm text-destructive">{{ form.errors.join_date }}</p>
                        </div>
                    </CardContent>
                </Card>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <Link :href="employeesIndex.url()">
                        <Button variant="outline" type="button">Batal</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Karyawan' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
