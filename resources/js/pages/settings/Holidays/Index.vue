<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CalendarDays, Plus, Pencil, Trash2, Globe, Building2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';

type Holiday = {
    id: number;
    name: string;
    holiday_date: string;
    type: 'national' | 'company';
    is_paid: boolean;
    year: number;
};

const props = defineProps<{
    holidays: Holiday[];
    year: number;
    years: number[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengaturan', href: '#' },
    { title: 'Hari Libur', href: '#' },
];

const showModal = ref(false);
const editTarget = ref<Holiday | null>(null);

const form = useForm({
    name: '',
    holiday_date: '',
    type: 'national' as 'national' | 'company',
    is_paid: true,
});

function openCreate() {
    editTarget.value = null;
    form.reset();
    form.type = 'national';
    form.is_paid = true;
    showModal.value = true;
}

function openEdit(h: Holiday) {
    editTarget.value = h;
    form.name = h.name;
    form.holiday_date = h.holiday_date;
    form.type = h.type;
    form.is_paid = h.is_paid;
    showModal.value = true;
}

function submit() {
    if (editTarget.value) {
        form.put(`/settings/holidays/${editTarget.value.id}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/settings/holidays', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function destroy(h: Holiday) {
    if (confirm(`Hapus "${h.name}"?`)) {
        router.delete(`/settings/holidays/${h.id}`);
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function changeYear(y: any) {
    router.get('/settings/holidays', { year: String(y ?? props.year) }, { preserveState: true });
}

const monthGroups = computed(() => {
    const groups: Record<string, Holiday[]> = {};
    props.holidays.forEach((h) => {
        const month = h.holiday_date.substring(0, 7); // YYYY-MM
        if (!groups[month]) groups[month] = [];
        groups[month].push(h);
    });
    return groups;
});

const monthLabel = (ym: string) => {
    const [y, m] = ym.split('-');
    return new Date(+y, +m - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const dayLabel = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Hari Libur" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <CalendarDays class="h-6 w-6 text-primary" />
                    <div>
                        <h1 class="text-xl font-bold">Kalender Hari Libur</h1>
                        <p class="text-sm text-muted-foreground">Master hari libur nasional & perusahaan</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Select :model-value="String(year)" @update:model-value="changeYear">
                        <SelectTrigger class="w-28">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="y in years" :key="y" :value="String(y)">{{ y }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button @click="openCreate"><Plus class="mr-2 h-4 w-4" /> Tambah Libur</Button>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-4">
                <Card>
                    <CardContent class="pt-4 text-center">
                        <p class="text-3xl font-bold text-primary">{{ holidays.length }}</p>
                        <p class="text-sm text-muted-foreground mt-1">Total Hari Libur</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ holidays.filter(h => h.type === 'national').length }}</p>
                        <p class="text-sm text-muted-foreground mt-1">Libur Nasional</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 text-center">
                        <p class="text-3xl font-bold text-orange-600">{{ holidays.filter(h => h.type === 'company').length }}</p>
                        <p class="text-sm text-muted-foreground mt-1">Libur Perusahaan</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="holidays.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                <CalendarDays class="h-12 w-12 text-muted-foreground mb-4" />
                <p class="text-muted-foreground">Belum ada hari libur untuk tahun {{ year }}</p>
                <Button class="mt-4" @click="openCreate"><Plus class="mr-2 h-4 w-4" /> Tambah Sekarang</Button>
            </div>

            <!-- Month Groups -->
            <div v-else class="space-y-4">
                <Card v-for="(items, month) in monthGroups" :key="month">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ monthLabel(month) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="h in items"
                            :key="h.id"
                            class="flex items-center justify-between rounded-lg border px-4 py-3 hover:bg-muted/40 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <component
                                    :is="h.type === 'national' ? Globe : Building2"
                                    class="h-4 w-4 shrink-0"
                                    :class="h.type === 'national' ? 'text-blue-500' : 'text-orange-500'"
                                />
                                <div>
                                    <p class="font-medium text-sm">{{ h.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ dayLabel(h.holiday_date) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="text-xs">
                                    {{ h.is_paid ? 'Berbayar' : 'Tidak Berbayar' }}
                                </Badge>
                                <Button variant="ghost" size="icon" class="h-7 w-7" @click="openEdit(h)">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive hover:text-destructive" @click="destroy(h)">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editTarget ? 'Edit Hari Libur' : 'Tambah Hari Libur' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1">
                        <Label>Nama Hari Libur</Label>
                        <Input v-model="form.name" placeholder="Hari Raya Idul Fitri" />
                        <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Tanggal</Label>
                        <Input type="date" v-model="form.holiday_date" />
                        <p v-if="form.errors.holiday_date" class="text-xs text-destructive">{{ form.errors.holiday_date }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Tipe</Label>
                        <Select v-model="form.type">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="national">🌐 Nasional</SelectItem>
                                <SelectItem value="company">🏢 Perusahaan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_paid" v-model="form.is_paid" class="h-4 w-4 rounded" />
                        <Label for="is_paid">Hari libur berbayar</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showModal = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editTarget ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
