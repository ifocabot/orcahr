<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Layers, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ref } from 'vue';

type Level = { id: number; name: string; level_order: number; is_active: boolean; employees_count: number };

const props = defineProps<{ jobLevels: Level[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Job Level', href: '/job-levels' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ name: '', level_order: 0, is_active: true });

const openCreate = () => { editingId.value = null; form.reset(); showModal.value = true; };
const openEdit = (l: Level) => {
    editingId.value = l.id;
    form.name = l.name; form.level_order = l.level_order; form.is_active = l.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/job-levels/${editingId.value}`, { onSuccess: () => { showModal.value = false; } });
    } else {
        form.post('/job-levels', { onSuccess: () => { showModal.value = false; } });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');

const remove = (id: number) => {
    deleteUrl.value = `/job-levels/${id}`;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Job Level" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl"><Layers class="h-5 w-5" /> Job Level</CardTitle>
                        <CardDescription>{{ jobLevels.length }} level</CardDescription>
                    </div>
                    <Button @click="openCreate"><Plus class="mr-2 h-4 w-4" /> Tambah</Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Urutan</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Karyawan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="l in jobLevels" :key="l.id">
                                    <TableCell class="text-center">{{ l.level_order }}</TableCell>
                                    <TableCell class="font-medium">{{ l.name }}</TableCell>
                                    <TableCell>{{ l.employees_count }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="l.is_active ? 'default' : 'secondary'">{{ l.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(l)"><Pencil class="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(l.id)"><Trash2 class="h-4 w-4" /></Button>
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
                <DialogHeader><DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Job Level</DialogTitle><DialogDescription>Isi data job level di bawah ini.</DialogDescription></DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="space-y-2">
                        <Label>Nama *</Label>
                        <Input v-model="form.name" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Urutan *</Label>
                        <Input v-model="form.level_order" type="number" min="0" />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDeleteDialog v-model:open="showDeleteDialog" :delete-url="deleteUrl" title="Hapus Job Level" />
    </AppLayout>
</template>
