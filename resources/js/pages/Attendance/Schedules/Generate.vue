<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarCog } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

type Employee = { id: number; full_name: string; employee_code: string };
type Shift = { id: number; name: string; start_time: string; end_time: string };

const props = defineProps<{
    employees: Employee[];
    shifts: Shift[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Jadwal', href: '/attendance/schedules' },
    { title: 'Generate', href: '/attendance/schedules/generate' },
];

const form = useForm({
    employee_ids: [] as number[],
    shift_id: '',
    start_date: '',
    end_date: '',
});

const toggleEmployee = (id: number) => {
    const idx = form.employee_ids.indexOf(id);
    if (idx === -1) form.employee_ids.push(id);
    else form.employee_ids.splice(idx, 1);
};

const selectAll = () => {
    if (form.employee_ids.length === props.employees.length) {
        form.employee_ids = [];
    } else {
        form.employee_ids = props.employees.map(e => e.id);
    }
};

const allSelected = computed(() => form.employee_ids.length === props.employees.length && props.employees.length > 0);

const selectedShift = computed(() => props.shifts.find(s => String(s.id) === form.shift_id));

const submit = () => {
    form.post('/attendance/schedules/generate');
};
</script>

<template>
    <Head title="Generate Jadwal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6 max-w-3xl">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-xl">
                        <CalendarCog class="h-5 w-5" /> Generate Jadwal
                    </CardTitle>
                    <CardDescription>Assign shift ke karyawan dalam rentang tanggal tertentu.</CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="grid gap-6">
                        <!-- Shift -->
                        <div class="space-y-2">
                            <Label>Shift *</Label>
                            <Select v-model="form.shift_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih shift..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in shifts" :key="s.id" :value="String(s.id)">
                                        {{ s.name }} ({{ s.start_time.slice(0,5) }} – {{ s.end_time.slice(0,5) }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.shift_id" class="text-sm text-destructive">{{ form.errors.shift_id }}</p>
                        </div>

                        <!-- Date range -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-2">
                                <Label>Tanggal Mulai *</Label>
                                <Input v-model="form.start_date" type="date" />
                                <p v-if="form.errors.start_date" class="text-sm text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Tanggal Selesai *</Label>
                                <Input v-model="form.end_date" type="date" :min="form.start_date" />
                                <p v-if="form.errors.end_date" class="text-sm text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Employee selection -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label>Karyawan * ({{ form.employee_ids.length }} dipilih)</Label>
                                <Button type="button" variant="ghost" size="sm" @click="selectAll">
                                    {{ allSelected ? 'Hapus Semua' : 'Pilih Semua' }}
                                </Button>
                            </div>
                            <p v-if="form.errors.employee_ids" class="text-sm text-destructive">{{ form.errors.employee_ids }}</p>
                            <div class="max-h-64 overflow-y-auto rounded-md border p-3 space-y-2">
                                <label
                                    v-for="e in employees"
                                    :key="e.id"
                                    class="flex items-center gap-3 cursor-pointer rounded p-1.5 hover:bg-muted"
                                >
                                    <Checkbox
                                        :checked="form.employee_ids.includes(e.id)"
                                        @update:checked="toggleEmployee(e.id)"
                                    />
                                    <div>
                                        <p class="text-sm font-medium">{{ e.full_name }}</p>
                                        <p class="text-xs text-muted-foreground font-mono">{{ e.employee_code }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div v-if="form.employee_ids.length > 0 && form.shift_id && form.start_date && form.end_date"
                             class="rounded-lg bg-muted/50 border p-4 text-sm space-y-1">
                            <p class="font-medium">Preview:</p>
                            <p>{{ form.employee_ids.length }} karyawan</p>
                            <p>Shift <strong>{{ selectedShift?.name }}</strong></p>
                            <p>{{ form.start_date }} s.d. {{ form.end_date }}</p>
                        </div>

                        <Button type="submit" :disabled="form.processing" class="w-full">
                            {{ form.processing ? 'Membuat Jadwal...' : 'Generate Jadwal' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
