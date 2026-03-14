<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { BarChart2, Download } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

type RecapRow = {
    employee_id: number; employee_code: string; full_name: string; department: string;
    total_days: number; present: number; late: number; absent: number;
    leave: number; holiday: number; late_minutes: number; ot_minutes: number; attendance_pct: number;
};

const props = defineProps<{
    recap: RecapRow[];
    departments: { id: number; name: string }[];
    filters: Record<string, string>;
    month: number;
    year: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Rekap Kehadiran', href: '#' },
];

const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const years  = Array.from({ length: 4 }, (_, i) => new Date().getFullYear() - 1 + i);

const selectedMonth = ref(String(props.month));
const selectedYear  = ref(String(props.year));
const selectedDept  = ref(props.filters.department_id ?? '');

function applyFilter() {
    router.get('/attendance/recap', {
        month: selectedMonth.value,
        year: selectedYear.value,
        department_id: selectedDept.value || undefined,
    }, { preserveState: true });
}

function exportExcel() {
    const params = new URLSearchParams({
        month: selectedMonth.value,
        year: selectedYear.value,
        ...(selectedDept.value ? { department_id: selectedDept.value } : {}),
    });
    window.open(`/attendance/recap/export?${params}`);
}

const pctColor = (p: number) => p >= 90 ? 'text-green-600' : p >= 75 ? 'text-yellow-600' : 'text-red-600';
</script>

<template>
    <Head title="Rekap Kehadiran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <BarChart2 class="h-6 w-6 text-primary" />
                    <div>
                        <h1 class="text-xl font-bold">Rekap Kehadiran</h1>
                        <p class="text-sm text-muted-foreground">{{ months[month - 1] }} {{ year }}</p>
                    </div>
                </div>
                <Button variant="outline" @click="exportExcel">
                    <Download class="mr-2 h-4 w-4" /> Export Excel
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-4">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="space-y-1">
                            <p class="text-xs text-muted-foreground font-medium">Bulan</p>
                            <Select v-model="selectedMonth">
                                <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(m, i) in months" :key="i" :value="String(i + 1)">{{ m }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-muted-foreground font-medium">Tahun</p>
                            <Select v-model="selectedYear">
                                <SelectTrigger class="w-28"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="y in years" :key="y" :value="String(y)">{{ y }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-muted-foreground font-medium">Departemen</p>
                            <Select v-model="selectedDept">
                                <SelectTrigger class="w-48"><SelectValue placeholder="— Semua" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">— Semua Departemen</SelectItem>
                                    <SelectItem v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button @click="applyFilter">Tampilkan</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card><CardContent class="pt-4 text-center">
                    <p class="text-2xl font-bold">{{ recap.length }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Karyawan</p>
                </CardContent></Card>
                <Card><CardContent class="pt-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ recap.reduce((a, r) => a + r.present, 0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Total Hadir</p>
                </CardContent></Card>
                <Card><CardContent class="pt-4 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ recap.reduce((a, r) => a + r.absent, 0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Total Absent</p>
                </CardContent></Card>
                <Card><CardContent class="pt-4 text-center">
                    <p class="text-2xl font-bold text-orange-600">{{ recap.reduce((a, r) => a + r.late, 0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Terlambat</p>
                </CardContent></Card>
            </div>

            <!-- Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Detail per Karyawan</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="recap.length === 0" class="py-10 text-center text-muted-foreground text-sm">
                        Tidak ada data kehadiran untuk periode ini.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-muted-foreground text-xs">
                                    <th class="py-2 text-left font-medium">Karyawan</th>
                                    <th class="py-2 text-left font-medium">Dept</th>
                                    <th class="py-2 text-center font-medium">Hadir</th>
                                    <th class="py-2 text-center font-medium">Telat</th>
                                    <th class="py-2 text-center font-medium">Absent</th>
                                    <th class="py-2 text-center font-medium">Cuti</th>
                                    <th class="py-2 text-center font-medium">Libur</th>
                                    <th class="py-2 text-center font-medium">Mnt Telat</th>
                                    <th class="py-2 text-center font-medium">% Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in recap" :key="r.employee_id" class="border-b last:border-0 hover:bg-muted/30">
                                    <td class="py-2.5">
                                        <p class="font-medium">{{ r.full_name }}</p>
                                        <p class="text-xs text-muted-foreground font-mono">{{ r.employee_code }}</p>
                                    </td>
                                    <td class="py-2.5 text-xs text-muted-foreground">{{ r.department }}</td>
                                    <td class="py-2.5 text-center font-medium text-green-700">{{ r.present }}</td>
                                    <td class="py-2.5 text-center text-yellow-700">{{ r.late }}</td>
                                    <td class="py-2.5 text-center text-red-700">{{ r.absent }}</td>
                                    <td class="py-2.5 text-center text-blue-700">{{ r.leave }}</td>
                                    <td class="py-2.5 text-center text-gray-500">{{ r.holiday }}</td>
                                    <td class="py-2.5 text-center tabular-nums">{{ r.late_minutes }}</td>
                                    <td class="py-2.5 text-center">
                                        <span :class="['font-bold tabular-nums', pctColor(r.attendance_pct)]">
                                            {{ r.attendance_pct }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
