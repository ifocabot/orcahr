<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Building2, Plus, Pencil, Trash2 } from 'lucide-vue-next';
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

type Dept = {
    id: number; name: string; code: string; parent_id: number | null; manager_id: number | null;
    is_active: boolean; employees_count: number;
    parent?: { id: number; name: string };
    manager?: { id: number; full_name: string };
};

const props = defineProps<{
    departments: Dept[];
    allDepartments: { id: number; name: string }[];
    allEmployees: { id: number; full_name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Departemen', href: '/departments' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '', code: '', parent_id: '', manager_id: '', is_active: true,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (d: Dept) => {
    editingId.value = d.id;
    form.name = d.name;
    form.code = d.code;
    form.parent_id = d.parent_id ? String(d.parent_id) : '__none__';
    form.manager_id = d.manager_id ? String(d.manager_id) : '__none__';
    form.is_active = d.is_active;
    showModal.value = true;
};

const submit = () => {
    const data = {
        ...form.data(),
        parent_id: form.parent_id === '__none__' ? null : form.parent_id,
        manager_id: form.manager_id === '__none__' ? null : form.manager_id,
    };
    if (editingId.value) {
        router.put(`/departments/${editingId.value}`, data, { onSuccess: () => { showModal.value = false; } });
    } else {
        router.post('/departments', data, { onSuccess: () => { showModal.value = false; } });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');

const remove = (id: number) => {
    deleteUrl.value = `/departments/${id}`;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Departemen" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl"><Building2 class="h-5 w-5" /> Departemen</CardTitle>
                        <CardDescription>{{ departments.length }} departemen</CardDescription>
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
                                    <TableHead>Parent</TableHead>
                                    <TableHead>Manager</TableHead>
                                    <TableHead>Karyawan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="d in departments" :key="d.id">
                                    <TableCell class="font-mono text-xs">{{ d.code }}</TableCell>
                                    <TableCell class="font-medium">{{ d.name }}</TableCell>
                                    <TableCell>{{ d.parent?.name ?? '-' }}</TableCell>
                                    <TableCell>{{ d.manager?.full_name ?? '-' }}</TableCell>
                                    <TableCell>{{ d.employees_count }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="d.is_active ? 'default' : 'secondary'">
                                            {{ d.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(d)">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(d.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
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
                    <DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Departemen</DialogTitle>
                    <DialogDescription>Isi data departemen di bawah ini.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="space-y-2">
                        <Label>Nama *</Label>
                        <Input v-model="form.name" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Kode *</Label>
                        <Input v-model="form.code" placeholder="HR, IT, FIN..." maxlength="10" />
                        <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Parent</Label>
                        <Select v-model="form.parent_id">
                            <SelectTrigger><SelectValue placeholder="Tidak ada" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">Tidak ada</SelectItem>
                                <SelectItem v-for="d in allDepartments" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>Manager</Label>
                        <Select v-model="form.manager_id">
                            <SelectTrigger><SelectValue placeholder="Pilih Manager" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">Tidak ada</SelectItem>
                                <SelectItem v-for="e in allEmployees" :key="e.id" :value="String(e.id)">{{ e.full_name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDeleteDialog v-model:open="showDeleteDialog" :delete-url="deleteUrl" title="Hapus Departemen" description="Apakah Anda yakin ingin menghapus departemen ini? Semua data terkait akan terpengaruh." />
    </AppLayout>
</template>
