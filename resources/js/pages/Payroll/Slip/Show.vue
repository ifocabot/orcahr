<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';

type SlipItem = { component_name: string; type: string; amount: number; notes: string | null };
type Run = { id: number; period_label: string; status: string } | null;
type Employee = { full_name: string; employee_code: string };

const props = defineProps<{
    run: Run;
    employee: Employee;
    earnings: SlipItem[];
    deductions: SlipItem[];
    gross: number;
    total_deductions: number;
    net: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payroll', href: '/payroll' },
    { title: 'Slip Gaji', href: '#' },
];

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
</script>

<template>
    <Head :title="`Slip Gaji — ${employee.full_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center justify-between print:hidden">
                <Link :href="run ? `/payroll/${run.id}/report` : '/payroll'">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-1 h-4 w-4" /> Kembali
                    </Button>
                </Link>
                <Button v-if="run" variant="outline" size="sm" onclick="window.print()">
                    <Printer class="mr-1 h-4 w-4" /> Cetak
                </Button>
            </div>

            <!-- No data state -->
            <div v-if="!run" class="mx-auto w-full max-w-2xl rounded-xl border bg-white p-8 text-center shadow">
                <p class="text-lg font-semibold text-gray-600">Belum Ada Data Slip Gaji</p>
                <p class="text-muted-foreground mt-1 text-sm">
                    Slip gaji Anda akan muncul setelah payroll diapprove oleh HR.
                </p>
            </div>

            <!-- Slip Card -->
            <div v-else class="mx-auto w-full max-w-2xl rounded-xl border bg-white p-8 shadow print:shadow-none">

                <!-- Header -->
                <div class="mb-6 border-b pb-6">
                    <h1 class="text-2xl font-bold text-gray-900">SLIP GAJI</h1>
                    <p class="text-gray-500">{{ run.period_label }}</p>
                </div>

                <!-- Employee info -->
                <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama Karyawan</p>
                        <p class="font-semibold">{{ employee.full_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kode Karyawan</p>
                        <p class="font-mono font-semibold">{{ employee.employee_code }}</p>
                    </div>
                </div>

                <!-- Earnings -->
                <div class="mb-4">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 uppercase tracking-wide">Pendapatan</h3>
                    <div class="space-y-1">
                        <div
                            v-for="item in earnings"
                            :key="item.component_name"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-gray-700">
                                {{ item.component_name }}
                                <span v-if="item.notes" class="text-gray-400 text-xs ml-1">({{ item.notes }})</span>
                            </span>
                            <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between border-t pt-2 text-sm font-semibold">
                        <span>Total Pendapatan</span>
                        <span>{{ formatCurrency(gross) }}</span>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="mb-6">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 uppercase tracking-wide">Potongan</h3>
                    <div class="space-y-1">
                        <div
                            v-for="item in deductions"
                            :key="item.component_name"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-gray-700">{{ item.component_name }}</span>
                            <span class="text-red-600 font-medium">- {{ formatCurrency(item.amount) }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between border-t pt-2 text-sm font-semibold">
                        <span>Total Potongan</span>
                        <span class="text-red-600">- {{ formatCurrency(total_deductions) }}</span>
                    </div>
                </div>

                <!-- Net -->
                <div class="rounded-lg bg-green-50 px-4 py-4 flex justify-between items-center">
                    <span class="font-bold text-green-800">GAJI BERSIH (NETO)</span>
                    <span class="text-xl font-bold text-green-700">{{ formatCurrency(net) }}</span>
                </div>

                <p class="mt-6 text-center text-xs text-gray-400">
                    Slip gaji ini diterbitkan secara elektronik dan sah tanpa tanda tangan.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .print\:hidden { display: none !important; }
    .print\:shadow-none { box-shadow: none !important; }
}
</style>
