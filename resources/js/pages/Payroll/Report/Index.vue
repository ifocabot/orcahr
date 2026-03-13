<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, ArrowLeft, Download } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Detail = { component_name: string; type: string; amount: number; notes: string | null };
type EmployeeRow = {
    id: number;
    full_name: string;
    employee_code: string;
    gross: number;
    deductions: number;
    net: number;
    details: Detail[];
};
type Run = { id: number; period_label: string; status: string; total_gross: number; total_net: number };

const props = defineProps<{ run: Run; employees: EmployeeRow[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payroll', href: '/payroll' },
    { title: 'Laporan - ' + props.run.period_label, href: '#' },
];

const STATUS_STYLE: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700', calculated: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-blue-100 text-blue-700', paid: 'bg-green-100 text-green-700',
};

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
</script>

<template>
    <Head :title="`Laporan Payroll — ${run.period_label}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/payroll">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-1 h-4 w-4" /> Kembali
                        </Button>
                    </Link>
                    <div>
                        <h1 class="flex items-center gap-2 text-xl font-bold">
                            <FileText class="h-5 w-5" /> Laporan Payroll {{ run.period_label }}
                        </h1>
                        <span :class="['mt-1 inline-block rounded px-2 py-0.5 text-xs font-medium', STATUS_STYLE[run.status] ?? '']">
                            {{ run.status.toUpperCase() }}
                        </span>
                    </div>
                </div>
                <a :href="`/payroll/${run.id}/export`">
                    <Button>
                        <Download class="mr-2 h-4 w-4" /> Export Excel
                    </Button>
                </a>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <p class="text-muted-foreground text-xs">Total Karyawan</p>
                        <p class="mt-1 text-2xl font-bold">{{ employees.length }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p class="text-muted-foreground text-xs">Total Bruto</p>
                        <p class="mt-1 text-lg font-bold">{{ formatCurrency(run.total_gross) }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p class="text-muted-foreground text-xs">Total Neto</p>
                        <p class="mt-1 text-lg font-bold text-green-600">{{ formatCurrency(run.total_net) }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Details Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Rincian Per Karyawan</CardTitle>
                    <CardDescription>Klik nama untuk melihat slip gaji</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Karyawan</TableHead>
                                    <TableHead>Total Pendapatan</TableHead>
                                    <TableHead>Total Potongan</TableHead>
                                    <TableHead>Gaji Bersih</TableHead>
                                    <TableHead class="w-[80px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="emp in employees" :key="emp.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <p class="font-medium text-sm">{{ emp.full_name }}</p>
                                        <p class="text-muted-foreground font-mono text-xs">{{ emp.employee_code }}</p>
                                    </TableCell>
                                    <TableCell>{{ formatCurrency(emp.gross) }}</TableCell>
                                    <TableCell class="text-red-600">{{ formatCurrency(emp.deductions) }}</TableCell>
                                    <TableCell class="font-semibold text-green-600">{{ formatCurrency(emp.net) }}</TableCell>
                                    <TableCell>
                                        <a :href="`/payroll/${run.id}/slip/${emp.id}`">
                                            <Button variant="ghost" size="sm">Slip</Button>
                                        </a>
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
