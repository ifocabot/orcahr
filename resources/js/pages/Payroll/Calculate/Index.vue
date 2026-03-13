<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Calculator, Play, CheckCircle, Banknote, FileSpreadsheet, Eye } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Run = {
    id: number;
    period_label: string;
    status: 'draft' | 'calculated' | 'approved' | 'paid';
    total_gross: number;
    total_net: number;
    calculated_at: string | null;
};

defineProps<{ runs: Run[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payroll', href: '#' },
    { title: 'Hitung Gaji', href: '/payroll' },
];

const STATUS_STYLE: Record<string, string> = {
    draft:      'bg-gray-100 text-gray-700',
    calculated: 'bg-yellow-100 text-yellow-700',
    approved:   'bg-blue-100 text-blue-700',
    paid:       'bg-green-100 text-green-700',
};
const STATUS_LABEL: Record<string, string> = {
    draft: 'Draft', calculated: 'Terhitung', approved: 'Diapprove', paid: 'Lunas',
};

const currentYear = new Date().getFullYear();
const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
const years = [currentYear - 1, currentYear, currentYear + 1];

const form = useForm({
    month: (new Date().getMonth() + 1).toString(),
    year:  currentYear.toString(),
});

const submit = () => {
    form.post('/payroll/calculate');
};

const approve = (id: number) => router.put(`/payroll/${id}/approve`);
const markPaid = (id: number) => router.put(`/payroll/${id}/paid`);

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
</script>

<template>
    <Head title="Hitung Gaji" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Calculate Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calculator class="h-5 w-5" /> Hitung Payroll
                    </CardTitle>
                    <CardDescription>Pilih periode dan jalankan kalkulasi gaji seluruh karyawan aktif.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="flex flex-wrap items-end gap-4">
                        <div class="space-y-2">
                            <Label>Bulan *</Label>
                            <Select v-model="form.month">
                                <SelectTrigger class="w-40"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(m, i) in months" :key="i" :value="(i+1).toString()">
                                        {{ m }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Tahun *</Label>
                            <Select v-model="form.year">
                                <SelectTrigger class="w-28"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="y in years" :key="y" :value="y.toString()">{{ y }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            <Play class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menghitung...' : 'Hitung Sekarang' }}
                        </Button>
                    </form>
                    <p v-if="(form.errors as any).calculate" class="text-destructive mt-2 text-sm">{{ (form.errors as any).calculate }}</p>
                </CardContent>
            </Card>

            <!-- Payroll Run History -->
            <Card>
                <CardHeader>
                    <CardTitle>Riwayat Payroll</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Periode</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Total Bruto</TableHead>
                                    <TableHead>Total Neto</TableHead>
                                    <TableHead>Dihitung</TableHead>
                                    <TableHead class="w-[200px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="run in runs" :key="run.id" class="hover:bg-muted/50">
                                    <TableCell class="font-medium">{{ run.period_label }}</TableCell>
                                    <TableCell>
                                        <span :class="['rounded px-2 py-0.5 text-xs font-medium', STATUS_STYLE[run.status]]">
                                            {{ STATUS_LABEL[run.status] }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ formatCurrency(run.total_gross) }}</TableCell>
                                    <TableCell class="font-semibold">{{ formatCurrency(run.total_net) }}</TableCell>
                                    <TableCell class="text-muted-foreground text-sm">
                                        {{ run.calculated_at ?? '—' }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1">
                                            <a :href="`/payroll/${run.id}/report`">
                                                <Button variant="outline" size="sm">
                                                    <Eye class="mr-1 h-3.5 w-3.5" /> Laporan
                                                </Button>
                                            </a>
                                            <Button
                                                v-if="run.status === 'calculated'"
                                                variant="outline" size="sm"
                                                @click="approve(run.id)"
                                            >
                                                <CheckCircle class="mr-1 h-3.5 w-3.5" /> Approve
                                            </Button>
                                            <Button
                                                v-if="run.status === 'approved'"
                                                variant="outline" size="sm"
                                                @click="markPaid(run.id)"
                                            >
                                                <Banknote class="mr-1 h-3.5 w-3.5" /> Tandai Lunas
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="runs.length === 0">
                                    <TableCell colspan="6" class="text-muted-foreground h-24 text-center">
                                        Belum ada payroll. Klik "Hitung Sekarang" untuk memulai.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
