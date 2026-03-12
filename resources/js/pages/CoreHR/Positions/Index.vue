<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Briefcase, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ref } from 'vue';

type Pos = {
    id: number; name: string; code: string; department_id: number | null;
    is_active: boolean; employees_count: number;
    department?: { id: number; name: string };
};

const props = defineProps<{
    positions: Pos[];
    departments: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jabatan', href: '/positions' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', code: '', department_id: '', is_active: true });

const openCreate = () => { editingId.value = null; form.reset(); showModal.value = true; };
const openEdit = (p: Pos) => {
    editingId.value = p.id;
    form.name = p.name; form.code = p.code;
    form.department_id = p.department_id ? String(p.department_id) : '__none__';
    form.is_active = p.is_active;
    showModal.value = true;
};

const submit = () => {
    const data = {
        ...form.data(),
        department_id: form.department_id === '__none__' ? null : form.department_id,
    };
    if (editingId.value) {
        router.put(`/positions/${editingId.value}`, data, { onSuccess: () => { showModal.value = false; } });
    } else {
        router.post('/positions', data, { onSuccess: () => { showModal.value = false; } });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');

const remove = (id: number) => {
    deleteUrl.value = `/positions/${id}`;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Jabatan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl"><Briefcase class="h-5 w-5" /> Jabatan</CardTitle>
                        <CardDescription>{{ positions.length }} jabatan</CardDescription>
                    </div>
                    <Button @click="openCreate"><Plus class="mr-2 h-4 w-4" /> Tambah</Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Departemen</TableHead>
                                    <TableHead>Karyawan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="p in positions" :key="p.id">
                                    <TableCell class="font-mono text-xs">{{ p.code }}</TableCell>
                                    <TableCell class="font-medium">{{ p.name }}</TableCell>
                                    <TableCell>{{ p.department?.name ?? '-' }}</TableCell>
                                    <TableCell>{{ p.employees_count }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="p.is_active ? 'default' : 'secondary'">{{ p.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(p)"><Pencil class="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(p.id)"><Trash2 class="h-4 w-4" /></Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="showModal">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Jabatan</DialogTitle><DialogDescription>Isi data jabatan di bawah ini.</DialogDescription></DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="space-y-2">
                        <Label>Nama *</Label>
                        <Input v-model="form.name" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Kode *</Label>
                        <Input v-model="form.code" maxlength="10" />
                        <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Departemen</Label>
                        <Select v-model="form.department_id">
                            <SelectTrigger><SelectValue placeholder="Pilih" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">Tidak ada</SelectItem>
                                <SelectItem v-for="d in departments" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDeleteDialog v-model:open="showDeleteDialog" :delete-url="deleteUrl" title="Hapus Jabatan" />
    </AppLayout>
</template>
