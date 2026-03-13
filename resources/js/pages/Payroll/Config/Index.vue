<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Settings2, Plus, Pencil, Trash2, ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Component = { id: number; name: string; code: string; type: string };
type Config = {
    id: number;
    component_id: number;
    amount: number;
    effective_date: string;
    end_date: string | null;
    component: Component;
};
type Employee = { id: number; full_name: string; employee_code: string };

const props = defineProps<{
    employee: Employee;
    configs: Config[];
    components: Component[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Karyawan', href: '/employees' },
    { title: props.employee.full_name, href: `/employees/${props.employee.id}` },
    { title: 'Konfigurasi Gaji', href: '#' },
];

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    component_id: '',
    amount: 0,
    effective_date: new Date().toISOString().split('T')[0],
    end_date: '',
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.effective_date = new Date().toISOString().split('T')[0];
    showModal.value = true;
};

const openEdit = (c: Config) => {
    editingId.value = c.id;
    form.component_id = c.component_id.toString();
    form.amount = c.amount;
    form.effective_date = c.effective_date;
    form.end_date = c.end_date ?? '';
    showModal.value = true;
};

const submit = () => {
    const empId = props.employee.id;
    if (editingId.value) {
        form.put(`/payroll/configs/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post(`/employees/${empId}/payroll-configs`, {
            onSuccess: () => { showModal.value = false; },
        });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');
const remove = (id: number) => {
    deleteUrl.value = `/payroll/configs/${id}`;
    showDeleteDialog.value = true;
};

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
</script>

<template>
    <Head :title="`Konfigurasi Gaji — ${employee.full_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Link :href="`/employees/${employee.id}`">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-1 h-4 w-4" /> Kembali
                    </Button>
                </Link>
                <div>
                    <h1 class="text-xl font-bold">{{ employee.full_name }}</h1>
                    <p class="text-muted-foreground font-mono text-sm">{{ employee.employee_code }}</p>
                </div>
            </div>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <Settings2 class="h-5 w-5" /> Konfigurasi Komponen Gaji
                        </CardTitle>
                        <CardDescription>{{ configs.length }} komponen dikonfigurasi</CardDescription>
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah
                    </Button>
                </CardHeader>

                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Komponen</TableHead>
                                    <TableHead>Tipe</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Efektif Dari</TableHead>
                                    <TableHead>Berakhir</TableHead>
                                    <TableHead class="w-[80px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="c in configs" :key="c.id" class="hover:bg-muted/50">
                                    <TableCell>
                                        <p class="font-medium text-sm">{{ c.component.name }}</p>
                                        <p class="text-muted-foreground font-mono text-xs">{{ c.component.code }}</p>
                                    </TableCell>
                                    <TableCell class="capitalize text-sm">{{ c.component.type }}</TableCell>
                                    <TableCell class="font-semibold text-sm">{{ formatCurrency(c.amount) }}</TableCell>
                                    <TableCell class="text-sm">{{ c.effective_date }}</TableCell>
                                    <TableCell class="text-muted-foreground text-sm">{{ c.end_date ?? '—' }}</TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(c)">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="remove(c.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="configs.length === 0">
                                    <TableCell colspan="6" class="text-muted-foreground h-24 text-center">
                                        Belum ada konfigurasi gaji. Klik "Tambah" untuk memulai.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Komponen Gaji</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="space-y-2">
                        <Label>Komponen *</Label>
                        <Select v-model="form.component_id" :disabled="!!editingId">
                            <SelectTrigger><SelectValue placeholder="Pilih komponen..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in components" :key="c.id" :value="c.id.toString()">
                                    {{ c.name }} ({{ c.code }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.component_id" class="text-destructive text-xs">{{ form.errors.component_id }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label>Amount (Rp) *</Label>
                        <Input v-model.number="form.amount" type="number" min="0" step="1000" />
                        <p v-if="form.errors.amount" class="text-destructive text-xs">{{ form.errors.amount }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Efektif Dari *</Label>
                            <Input v-model="form.effective_date" type="date" />
                        </div>
                        <div class="space-y-2">
                            <Label>Berakhir</Label>
                            <Input v-model="form.end_date" type="date" />
                        </div>
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
            title="Hapus Konfigurasi"
            description="Konfigurasi ini akan dihapus permanen."
        />
    </AppLayout>
</template>
