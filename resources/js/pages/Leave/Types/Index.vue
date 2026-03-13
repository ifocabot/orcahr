<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Tags, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type LeaveType = {
    id: number;
    name: string;
    code: string;
    accrual_rate_monthly: number;
    max_balance: number;
    max_carryover: number;
    min_service_months: number;
    is_paid: boolean;
    is_active: boolean;
};

defineProps<{ leaveTypes: LeaveType[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Jenis Cuti', href: '/leave/types' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    code: '',
    accrual_rate_monthly: 1.00,
    max_balance: 12,
    max_carryover: 6,
    min_service_months: 12,
    is_paid: true,
    is_active: true,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (t: LeaveType) => {
    editingId.value = t.id;
    form.name = t.name;
    form.code = t.code;
    form.accrual_rate_monthly = t.accrual_rate_monthly;
    form.max_balance = t.max_balance;
    form.max_carryover = t.max_carryover;
    form.min_service_months = t.min_service_months;
    form.is_paid = t.is_paid;
    form.is_active = t.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/leave/types/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/leave/types', {
            onSuccess: () => { showModal.value = false; },
        });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');
const remove = (id: number) => {
    deleteUrl.value = `/leave/types/${id}`;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Jenis Cuti" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Tags class="h-5 w-5" /> Jenis Cuti
                        </CardTitle>
                        <CardDescription>{{ leaveTypes.length }} jenis cuti terdaftar</CardDescription>
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Jenis
                    </Button>
                </CardHeader>

                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Akrual/Bln</TableHead>
                                    <TableHead>Maks Saldo</TableHead>
                                    <TableHead>Maks Carry</TableHead>
                                    <TableHead>Min. Masa Kerja</TableHead>
                                    <TableHead>Berbayar</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="t in leaveTypes" :key="t.id" class="hover:bg-muted/50">
                                    <TableCell class="font-medium">{{ t.name }}</TableCell>
                                    <TableCell><span class="bg-muted rounded px-1.5 py-0.5 font-mono text-xs">{{ t.code }}</span></TableCell>
                                    <TableCell>{{ t.accrual_rate_monthly > 0 ? `${t.accrual_rate_monthly} hari` : '—' }}</TableCell>
                                    <TableCell>{{ t.max_balance > 0 ? `${t.max_balance} hari` : '∞' }}</TableCell>
                                    <TableCell>{{ t.max_carryover > 0 ? `${t.max_carryover} hari` : '—' }}</TableCell>
                                    <TableCell>{{ t.min_service_months > 0 ? `${t.min_service_months} bln` : 'Langsung' }}</TableCell>
                                    <TableCell>
                                        <StatusBadge :status="t.is_paid ? 'active' : 'inactive'" />
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge :status="t.is_active ? 'active' : 'inactive'" />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(t)">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(t.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="leaveTypes.length === 0">
                                    <TableCell colspan="9" class="text-muted-foreground h-24 text-center">
                                        Belum ada jenis cuti.
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
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Jenis Cuti</DialogTitle>
                    <DialogDescription>Konfigurasi tipe cuti dan aturan akrualnya.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Nama *</Label>
                            <Input v-model="form.name" placeholder="Cuti Tahunan..." />
                            <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Kode *</Label>
                            <Input v-model="form.code" placeholder="CT" maxlength="10" class="font-mono uppercase" />
                            <p v-if="form.errors.code" class="text-destructive text-xs">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-2">
                            <Label>Akrual/Bln</Label>
                            <Input v-model.number="form.accrual_rate_monthly" type="number" min="0" max="99" step="0.5" />
                        </div>
                        <div class="space-y-2">
                            <Label>Maks Saldo</Label>
                            <Input v-model.number="form.max_balance" type="number" min="0" />
                        </div>
                        <div class="space-y-2">
                            <Label>Maks Carry</Label>
                            <Input v-model.number="form.max_carryover" type="number" min="0" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Min. Masa Kerja (bulan)</Label>
                        <Input v-model.number="form.min_service_months" type="number" min="0" />
                    </div>

                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="text-sm font-medium">Cuti Berbayar</p>
                            <p class="text-muted-foreground text-xs">Gaji tetap berjalan saat cuti</p>
                        </div>
                        <Switch v-model:checked="form.is_paid" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="text-sm font-medium">Aktif</p>
                            <p class="text-muted-foreground text-xs">Bisa dipilih saat pengajuan</p>
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
            title="Hapus Jenis Cuti"
            description="Jenis cuti yang sudah digunakan tidak dapat dihapus."
        />
    </AppLayout>
</template>
