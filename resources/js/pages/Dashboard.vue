<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, Clock, CalendarX, Coins, TrendingUp } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Stats = {
    total_employees: number;
    present_today: number;
    pending_leaves: number;
    pending_payroll: number;
};
type ClockIn = {
    employee_name: string;
    employee_code: string;
    event_time: string;
    selfie_path: string | null;
};
type DeptCount = { department: string; count: number };

const props = defineProps<{
    stats: Stats;
    recent_clock_ins: ClockIn[];
    headcount_by_dept: DeptCount[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard() }];

const maxCount = props.headcount_by_dept.length
    ? Math.max(...props.headcount_by_dept.map((d) => d.count))
    : 1;

const statCards = [
    {
        title: 'Total Karyawan',
        value: props.stats.total_employees,
        icon: Users,
        color: 'text-blue-600',
        bg: 'bg-blue-50 dark:bg-blue-950',
        href: '/employees',
    },
    {
        title: 'Hadir Hari Ini',
        value: props.stats.present_today,
        icon: Clock,
        color: 'text-green-600',
        bg: 'bg-green-50 dark:bg-green-950',
        href: '/attendance/timesheet',
    },
    {
        title: 'Cuti Pending',
        value: props.stats.pending_leaves,
        icon: CalendarX,
        color: 'text-orange-600',
        bg: 'bg-orange-50 dark:bg-orange-950',
        href: '/leave/requests/approval',
    },
    {
        title: 'Payroll Awaiting Approval',
        value: props.stats.pending_payroll,
        icon: Coins,
        color: 'text-purple-600',
        bg: 'bg-purple-50 dark:bg-purple-950',
        href: '/payroll',
    },
];
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.title"
                    :href="card.href"
                    class="group"
                >
                    <Card class="transition-shadow hover:shadow-md cursor-pointer">
                        <CardContent class="flex items-center gap-4 p-5">
                            <div :class="['rounded-xl p-3 transition-transform group-hover:scale-110', card.bg]">
                                <component :is="card.icon" :class="['h-6 w-6', card.color]" />
                            </div>
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">{{ card.title }}</p>
                                <p class="mt-0.5 text-2xl font-bold tracking-tight">{{ card.value }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">

                <!-- Headcount by Department -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <TrendingUp class="h-4 w-4" /> Headcount per Departemen
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="headcount_by_dept.length === 0" class="text-muted-foreground py-8 text-center text-sm">
                            Belum ada data departemen.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="dept in headcount_by_dept"
                                :key="dept.department"
                                class="flex items-center gap-3"
                            >
                                <span class="w-36 shrink-0 truncate text-sm text-right text-muted-foreground">
                                    {{ dept.department }}
                                </span>
                                <div class="flex-1 rounded-full bg-muted h-2.5 overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{ width: `${(dept.count / maxCount) * 100}%` }"
                                    />
                                </div>
                                <span class="w-6 shrink-0 text-sm font-semibold tabular-nums">{{ dept.count }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Clock-ins -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Clock class="h-4 w-4" /> Clock-In Terbaru Hari Ini
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent_clock_ins.length === 0" class="text-muted-foreground py-8 text-center text-sm">
                            Belum ada clock-in hari ini.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="item in recent_clock_ins"
                                :key="item.employee_code + item.event_time"
                                class="flex items-center gap-3"
                            >
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground text-xs font-bold uppercase select-none">
                                    {{ item.employee_name.charAt(0) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ item.employee_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ item.employee_code }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700 tabular-nums dark:bg-green-950 dark:text-green-400">
                                    {{ item.event_time }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>
