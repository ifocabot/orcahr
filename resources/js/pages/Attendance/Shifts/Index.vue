<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Clock, Plus, Pencil, Trash2, Moon } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Switch } from '@/components/ui/switch';
import { useForm } from '@inertiajs/vue3';

type Shift = {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    is_overnight: boolean;
    break_minutes: number;
    overtime_threshold_minutes: number;
    is_active: boolean;
};

defineProps<{ shifts: Shift[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Absensi', href: '#' },
    { title: 'Shift', href: '/attendance/shifts' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    start_time: '08:00',
    end_time: '17:00',
    is_overnight: false,
    break_minutes: 60,
    overtime_threshold_minutes: 30,
    is_active: true,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (s: Shift) => {
    editingId.value = s.id;
    form.name = s.name;
    form.start_time = s.start_time.slice(0, 5);
    form.end_time = s.end_time.slice(0, 5);
    form.is_overnight = s.is_overnight;
    form.break_minutes = s.break_minutes;
    form.overtime_threshold_minutes = s.overtime_threshold_minutes;
    form.is_active = s.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/attendance/shifts/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/attendance/shifts', {
            onSuccess: () => { showModal.value = false; },
        });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');
const remove = (id: number) => {
    deleteUrl.value = `/attendance/shifts/${id}`;
    showDeleteDialog.value = true;
};

const formatTime = (t: string) => t.slice(0, 5);
</script>

<template>
    <Head title="Shift" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Clock class="h-5 w-5" /> Shift
                        </CardTitle>
                        <CardDescription>{{ shifts.length }} template shift</CardDescription>
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Shift
                    </Button>
                </CardHeader>

                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Jam Mulai</TableHead>
                                    <TableHead>Jam Selesai</TableHead>
                                    <TableHead>Istirahat</TableHead>
                                    <TableHead>Min. OT</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="s in shifts" :key="s.id" class="hover:bg-muted/50">
                                    <TableCell class="font-medium">
                                        <span class="flex items-center gap-1.5">
                                            <Moon v-if="s.is_overnight" class="h-3.5 w-3.5 text-indigo-500" />
                                            {{ s.name }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="font-mono">{{ formatTime(s.start_time) }}</TableCell>
                                    <TableCell class="font-mono">{{ formatTime(s.end_time) }}</TableCell>
                                    <TableCell>{{ s.break_minutes }} menit</TableCell>
                                    <TableCell>{{ s.overtime_threshold_minutes }} menit</TableCell>
                                    <TableCell>
                                        <StatusBadge :status="s.is_active ? 'active' : 'inactive'" />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(s)">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(s.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="shifts.length === 0">
                                    <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                        Belum ada shift. Klik "Tambah Shift" untuk memulai.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modal CRUD -->
        <Dialog v-model:open="showModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Shift</DialogTitle>
                    <DialogDescription>Isi detail template shift kerja.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="space-y-2">
                        <Label>Nama Shift *</Label>
                        <Input v-model="form.name" placeholder="Pagi, Siang, Malam..." />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Jam Mulai *</Label>
                            <Input v-model="form.start_time" type="time" />
                            <p v-if="form.errors.start_time" class="text-sm text-destructive">{{ form.errors.start_time }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Jam Selesai *</Label>
                            <Input v-model="form.end_time" type="time" />
                            <p v-if="form.errors.end_time" class="text-sm text-destructive">{{ form.errors.end_time }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Istirahat (menit)</Label>
                            <Input v-model.number="form.break_minutes" type="number" min="0" max="480" />
                        </div>
                        <div class="space-y-2">
                            <Label>Min. Lembur (menit)</Label>
                            <Input v-model.number="form.overtime_threshold_minutes" type="number" min="0" max="120" />
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="text-sm font-medium">Shift Malam</p>
                            <p class="text-xs text-muted-foreground">Shift melewati tengah malam</p>
                        </div>
                        <Switch v-model:checked="form.is_overnight" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="text-sm font-medium">Aktif</p>
                            <p class="text-xs text-muted-foreground">Shift dapat digunakan untuk jadwal</p>
                        </div>
                        <Switch v-model:checked="form.is_active" />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDeleteDialog
            v-model:open="showDeleteDialog"
            :delete-url="deleteUrl"
            title="Hapus Shift"
            description="Shift yang masih digunakan jadwal tidak dapat dihapus."
        />
    </AppLayout>
</template>
