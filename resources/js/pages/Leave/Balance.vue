<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Wallet, CalendarDays, TrendingUpDown } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

type Balance = {
    leave_type_id: number;
    leave_type_name: string;
    leave_type_code: string;
    is_paid: boolean;
    max_balance: number;
    opening_balance: number;
    accrued: number;
    used: number;
    adjustment: number;
    closing_balance: number;
    expiry_date: string | null;
};

type Employee = { id: number; full_name: string; employee_code: string };

const props = defineProps<{
    balances: Balance[];
    employees: Employee[];
    currentEmployee: Employee | null;
    year: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Saldo Cuti', href: '/leave/balance' },
];

const selectedEmployee = ref(props.currentEmployee?.id?.toString() ?? '');
const selectedYear = ref(props.year.toString());

const years = Array.from({ length: 3 }, (_, i) => new Date().getFullYear() - i);

function applyFilters() {
    router.get('/leave/balance', {
        employee_id: selectedEmployee.value || undefined,
        year: selectedYear.value,
    }, { preserveState: true, replace: true });
}

watch([selectedEmployee, selectedYear], applyFilters);

function usagePercent(balance: Balance) {
    if (!balance.max_balance) return 0;
    return Math.round((balance.used / balance.max_balance) * 100);
}
</script>

<template>
    <Head title="Saldo Cuti" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Saldo Cuti</h1>
                    <p class="text-muted-foreground mt-1 text-sm">
                        {{ currentEmployee?.full_name ?? 'Semua Karyawan' }} · Tahun {{ year }}
                    </p>
                </div>
                <Link href="/leave/requests/create">
                    <Button>Ajukan Cuti</Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <Select v-model="selectedEmployee">
                    <SelectTrigger class="w-[220px]">
                        <SelectValue placeholder="Pilih Karyawan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="emp in employees" :key="emp.id" :value="emp.id.toString()">
                            {{ emp.full_name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedYear">
                    <SelectTrigger class="w-[120px]">
                        <SelectValue placeholder="Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="y in years" :key="y" :value="y.toString()">{{ y }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Balance Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="balance in balances" :key="balance.leave_type_id" class="relative overflow-hidden">
                    <CardHeader class="pb-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-base">{{ balance.leave_type_name }}</CardTitle>
                                <CardDescription class="mt-0.5 text-xs font-mono">{{ balance.leave_type_code }}</CardDescription>
                            </div>
                            <Badge :variant="balance.is_paid ? 'default' : 'secondary'" class="text-xs">
                                {{ balance.is_paid ? 'Berbayar' : 'Tidak Berbayar' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <!-- Big balance number -->
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold">{{ balance.closing_balance }}</span>
                            <span class="text-muted-foreground text-sm">/ {{ balance.max_balance || '∞' }} hari</span>
                        </div>

                        <!-- Progress bar -->
                        <div v-if="balance.max_balance" class="bg-muted h-1.5 w-full rounded-full">
                            <div
                                class="h-1.5 rounded-full bg-primary transition-all"
                                :style="{ width: `${Math.min(usagePercent(balance), 100)}%` }"
                            />
                        </div>

                        <!-- Stats row -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-muted-foreground text-xs">Akrual</p>
                                <p class="text-sm font-semibold text-green-600">+{{ balance.accrued }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground text-xs">Terpakai</p>
                                <p class="text-sm font-semibold text-red-500">{{ balance.used }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground text-xs">Sisa</p>
                                <p class="text-sm font-bold">{{ balance.closing_balance }}</p>
                            </div>
                        </div>

                        <!-- Expiry -->
                        <p v-if="balance.expiry_date" class="text-muted-foreground flex items-center gap-1 text-xs">
                            <CalendarDays class="h-3 w-3" />
                            Berakhir: {{ balance.expiry_date }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty state -->
            <div v-if="balances.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                <Wallet class="text-muted-foreground mb-3 h-12 w-12" />
                <p class="text-muted-foreground text-sm">Belum ada data saldo cuti untuk tahun ini.</p>
            </div>
        </div>
    </AppLayout>
</template>
