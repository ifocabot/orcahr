<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Layers, Plus, Pencil, Trash2 } from 'lucide-vue-next';
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

type Grade = {
    id: number;
    position?: { id: number; name: string };
    job_level?: { id: number; name: string };
    component: { id: number; name: string; code: string };
    default_amount: number;
    is_mandatory: boolean;
    is_active: boolean;
    notes?: string;
};

const props = defineProps<{
    grades: Grade[];
    positions: { id: number; name: string }[];
    jobLevels: { id: number; name: string }[];
    components: { id: number; name: string; code: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payroll', href: '#' },
    { title: 'Skema Gaji', href: '#' },
];

const fmt = (n: number) => new Intl.NumberFormat('id-ID').format(n);

const showModal = ref(false);
const editTarget = ref<Grade | null>(null);

const form = useForm({
    position_id: '' as string | number,
    job_level_id: '' as string | number,
    component_id: '' as string | number,
    default_amount: '' as string | number,
    is_mandatory: false,
    notes: '',
    is_active: true,
});

function openCreate() {
    editTarget.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(g: Grade) {
    editTarget.value = g;
    form.position_id = g.position?.id ?? '';
    form.job_level_id = g.job_level?.id ?? '';
    form.component_id = g.component.id;
    form.default_amount = g.default_amount;
    form.is_mandatory = g.is_mandatory;
    form.notes = g.notes ?? '';
    form.is_active = g.is_active;
    showModal.value = true;
}

function submit() {
    if (editTarget.value) {
        form.put(`/payroll/salary-grades/${editTarget.value.id}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/payroll/salary-grades', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function destroy(g: Grade) {
    if (confirm(`Hapus skema "${g.component.name}"?`)) {
        router.delete(`/payroll/salary-grades/${g.id}`);
    }
}
</script>

<template>
    <Head title="Skema Gaji" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Layers class="h-6 w-6 text-primary" />
                    <div>
                        <h1 class="text-xl font-bold">Skema Gaji</h1>
                        <p class="text-sm text-muted-foreground">Komponen gaji default per Jabatan & Level</p>
                    </div>
                </div>
                <Button @click="openCreate"><Plus class="mr-2 h-4 w-4" /> Tambah Skema</Button>
            </div>

            <!-- Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Daftar Skema Gaji ({{ grades.length }} entri)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="grades.length === 0" class="py-10 text-center text-muted-foreground text-sm">
                        Belum ada skema gaji. Tambahkan untuk mengatur komponen gaji default per jabatan.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-muted-foreground">
                                    <th class="py-2 text-left font-medium">Jabatan</th>
                                    <th class="py-2 text-left font-medium">Level</th>
                                    <th class="py-2 text-left font-medium">Komponen</th>
                                    <th class="py-2 text-right font-medium">Nominal Default</th>
                                    <th class="py-2 text-center font-medium">Wajib</th>
                                    <th class="py-2 text-center font-medium">Status</th>
                                    <th class="py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="g in grades" :key="g.id" class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                                    <td class="py-2.5">{{ g.position?.name ?? '— Semua Jabatan' }}</td>
                                    <td class="py-2.5">{{ g.job_level?.name ?? '— Semua Level' }}</td>
                                    <td class="py-2.5">
                                        <span class="font-medium">{{ g.component.name }}</span>
                                        <span class="ml-1 text-xs text-muted-foreground font-mono">({{ g.component.code }})</span>
                                    </td>
                                    <td class="py-2.5 text-right font-mono">Rp {{ fmt(g.default_amount) }}</td>
                                    <td class="py-2.5 text-center">
                                        <Badge :variant="g.is_mandatory ? 'default' : 'outline'" class="text-xs">
                                            {{ g.is_mandatory ? 'Wajib' : 'Opsional' }}
                                        </Badge>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span :class="['inline-block h-2 w-2 rounded-full', g.is_active ? 'bg-green-500' : 'bg-gray-300']" />
                                    </td>
                                    <td class="py-2.5">
                                        <div class="flex justify-end gap-1">
                                            <Button variant="ghost" size="icon" class="h-7 w-7" @click="openEdit(g)">
                                                <Pencil class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive hover:text-destructive" @click="destroy(g)">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ editTarget ? 'Edit Skema Gaji' : 'Tambah Skema Gaji' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Jabatan (opsional)</Label>
                            <Select v-model="form.position_id">
                                <SelectTrigger><SelectValue placeholder="— Semua Jabatan" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">— Semua Jabatan</SelectItem>
                                    <SelectItem v-for="p in positions" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Level (opsional)</Label>
                            <Select v-model="form.job_level_id">
                                <SelectTrigger><SelectValue placeholder="— Semua Level" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">— Semua Level</SelectItem>
                                    <SelectItem v-for="l in jobLevels" :key="l.id" :value="String(l.id)">{{ l.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Komponen Gaji <span class="text-destructive">*</span></Label>
                        <Select v-model="form.component_id">
                            <SelectTrigger><SelectValue placeholder="Pilih komponen" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in components" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.component_id" class="text-xs text-destructive">{{ form.errors.component_id }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Nominal Default (Rp) <span class="text-destructive">*</span></Label>
                        <Input type="number" v-model="form.default_amount" min="0" step="10000" placeholder="5000000" />
                        <p v-if="form.errors.default_amount" class="text-xs text-destructive">{{ form.errors.default_amount }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Catatan</Label>
                        <Input v-model="form.notes" placeholder="Opsional" />
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_mandatory" class="h-4 w-4 rounded" />
                            <span class="text-sm">Komponen wajib</span>
                        </label>
                        <label v-if="editTarget" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="h-4 w-4 rounded" />
                            <span class="text-sm">Aktif</span>
                        </label>
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
