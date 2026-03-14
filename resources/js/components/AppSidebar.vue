<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Users, Building2, Briefcase, Clock, CalendarDays, CalendarCheck2, ClipboardList, AlertTriangle, LogIn, Wallet, FilePlus, CheckSquare, Clock3, Tags, Coins, Receipt, Calculator, Layers, Settings2 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
];

const coreHRNavItems: NavItem[] = [
    { title: 'Karyawan',   href: '/employees',  icon: Users },
    { title: 'Departemen', href: '/departments', icon: Building2 },
    { title: 'Jabatan',    href: '/positions',   icon: Briefcase },
];

const attendanceNavItems: NavItem[] = [
    { title: 'Clock In/Out',  href: '/attendance/clock',      icon: LogIn },
    { title: 'Shift',         href: '/attendance/shifts',     icon: Clock },
    { title: 'Jadwal',        href: '/attendance/schedules',  icon: CalendarDays },
    { title: 'Timesheet',     href: '/attendance/timesheet',  icon: ClipboardList },
    { title: 'Pengecualian',  href: '/attendance/exceptions', icon: AlertTriangle },
    { title: 'Rekap Kehadiran', href: '/attendance/recap',    icon: AlertTriangle },
];

const leaveNavItems: NavItem[] = [
    { title: 'Saldo Cuti',    href: '/leave/balance',            icon: Wallet },
    { title: 'Ajukan Cuti',   href: '/leave/requests/create',    icon: FilePlus },
    { title: 'Riwayat Cuti',  href: '/leave/requests',           icon: CalendarCheck2 },
    { title: 'Approval Cuti', href: '/leave/requests/approval',  icon: CheckSquare },
    { title: 'Izin ½ Hari',   href: '/leave/half-day/create',    icon: Clock3 },
    { title: 'Jenis Cuti',    href: '/leave/types',              icon: Tags },
];

const payrollNavItems: NavItem[] = [
    { title: 'Komponen Gaji', href: '/payroll/components',    icon: Coins },
    { title: 'Skema Gaji',    href: '/payroll/salary-grades', icon: Layers },
    { title: 'Hitung Gaji',   href: '/payroll',               icon: Calculator },
    { title: 'Slip Saya',     href: '/payroll/my-slip',       icon: Receipt },
];

const settingsNavItems: NavItem[] = [
    { title: 'Hari Libur', href: '/settings/holidays', icon: CalendarDays },
    { title: 'Sistem',     href: '/settings/system',   icon: Settings2 },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="coreHRNavItems" label="Core HR" />
            <NavMain :items="attendanceNavItems" label="Absensi" />
            <NavMain :items="leaveNavItems" label="Cuti" />
            <NavMain :items="payrollNavItems" label="Payroll" />
            <NavMain :items="settingsNavItems" label="Pengaturan" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
