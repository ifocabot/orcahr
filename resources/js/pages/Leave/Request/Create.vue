<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarDays, Info } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';

type LeaveType = { id: number; name: string; code: string; max_balance: number };
type Balance = { leave_type_id: number; leave_type_name: string; available: number };

const props = defineProps<{
    leaveTypes: LeaveType[];
    balances: Balance[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Ajukan Cuti', href: '/leave/requests/create' },
];

const form = useForm({
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
});

const availableBalance = computed(() => {
    if (!form.leave_type_id) return null;
    return props.balances.find(b => b.leave_type_id === Number(form.leave_type_id));
});

const totalDays = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    if (end < start) return 0;

    let count = 0;
    const current = new Date(start);
    while (current <= end) {
        const day = current.getDay();
        if (day !== 0 && day !== 6) count++;
        current.setDate(current.getDate() + 1);
    }
    return count;
});

const balanceInsufficient = computed(() => {
    if (!availableBalance.value) return false;
    return totalDays.value > availableBalance.value.available;
});

function submit() {
    form.post('/leave/requests');
}
</script>

<template>
    <Head title="Ajukan Cuti" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CalendarDays class="h-5 w-5" /> Ajukan Cuti
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Leave Type -->
                            <div class="space-y-1.5">
                                <Label>Jenis Cuti</Label>
                                <Select v-model="form.leave_type_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih jenis cuti..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in leaveTypes"
                                            :key="type.id"
                                            :value="type.id.toString()"
                                        >
                                            {{ type.name }} ({{ type.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.leave_type_id" class="text-destructive text-xs">{{ form.errors.leave_type_id }}</p>
                            </div>

                            <!-- Balance Info -->
                            <Alert v-if="availableBalance" :class="balanceInsufficient ? 'border-destructive' : 'border-green-500'">
                                <Info class="h-4 w-4" />
                                <AlertDescription>
                                    Saldo tersedia: <strong>{{ availableBalance.available }} hari</strong>
                                    <span v-if="totalDays > 0"> · Diajukan: <strong>{{ totalDays }} hari</strong></span>
                                    <span v-if="balanceInsufficient" class="text-destructive ml-1 font-medium">— Saldo tidak cukup!</span>
                                </AlertDescription>
                            </Alert>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label>Tanggal Mulai</Label>
                                    <Input type="date" v-model="form.start_date" :min="new Date().toISOString().split('T')[0]" />
                                    <p v-if="form.errors.start_date" class="text-destructive text-xs">{{ form.errors.start_date }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Tanggal Selesai</Label>
                                    <Input type="date" v-model="form.end_date" :min="form.start_date" />
                                    <p v-if="form.errors.end_date" class="text-destructive text-xs">{{ form.errors.end_date }}</p>
                                </div>
                            </div>

                            <!-- Total days preview -->
                            <p v-if="totalDays > 0" class="text-muted-foreground text-sm">
                                Total: <strong class="text-foreground">{{ totalDays }} hari kerja</strong>
                            </p>

                            <!-- Reason -->
                            <div class="space-y-1.5">
                                <Label>Alasan <span class="text-muted-foreground">(opsional)</span></Label>
                                <textarea
                                    v-model="form.reason"
                                    placeholder="Tuliskan alasan cuti..."
                                    rows="3"
                                    class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                /></div>

                            <p v-if="form.errors.total_days" class="text-destructive text-sm">{{ form.errors.total_days }}</p>

                            <div class="flex justify-end gap-3 pt-2">
                                <Button type="button" variant="outline" @click="router.visit('/leave/requests')">Batal</Button>
                                <Button type="submit" :disabled="form.processing || balanceInsufficient">
                                    {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
