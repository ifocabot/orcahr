<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Coins, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Component = {
    id: number;
    name: string;
    code: string;
    type: 'earning' | 'deduction' | 'benefit';
    is_taxable: boolean;
    is_fixed: boolean;
    formula: string | null;
    is_active: boolean;
    sort_order: number;
};

defineProps<{ components: Component[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payroll', href: '#' },
    { title: 'Komponen Gaji', href: '/payroll/components' },
];

const TYPE_BADGE: Record<string, string> = {
    earning:   'bg-green-100 text-green-700',
    deduction: 'bg-red-100 text-red-700',
    benefit:   'bg-blue-100 text-blue-700',
};
const TYPE_LABEL: Record<string, string> = {
    earning: 'Pendapatan', deduction: 'Potongan', benefit: 'Benefit',
};

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    code: '',
    type: 'earning',
    is_taxable: true,
    is_fixed: true,
    formula: '',
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.type = 'earning';
    form.is_taxable = true;
    form.is_fixed = true;
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (c: Component) => {
    editingId.value = c.id;
    form.name = c.name;
    form.code = c.code;
    form.type = c.type;
    form.is_taxable = c.is_taxable;
    form.is_fixed = c.is_fixed;
    form.formula = c.formula ?? '';
    form.is_active = c.is_active;
    form.sort_order = c.sort_order;
    showModal.value = true;
};

const submit = () => {
    if (editingId.value) {
        form.put(`/payroll/components/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/payroll/components', {
            onSuccess: () => { showModal.value = false; },
        });
    }
};

const showDeleteDialog = ref(false);
const deleteUrl = ref('');
const remove = (id: number) => {
    deleteUrl.value = `/payroll/components/${id}`;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head title="Komponen Gaji" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-xl">
                            <Coins class="h-5 w-5" /> Komponen Gaji
                        </CardTitle>
                        <CardDescription>{{ components.length }} komponen terdaftar</CardDescription>
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Komponen
                    </Button>
                </CardHeader>

                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Tipe</TableHead>
                                    <TableHead>Kena Pajak</TableHead>
                                    <TableHead>Fixed</TableHead>
                                    <TableHead>Formula</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="c in components" :key="c.id" class="hover:bg-muted/50">
                                    <TableCell class="font-medium">{{ c.name }}</TableCell>
                                    <TableCell>
                                        <span class="bg-muted rounded px-1.5 py-0.5 font-mono text-xs">{{ c.code }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <span :class="['rounded px-2 py-0.5 text-xs font-medium', TYPE_BADGE[c.type]]">
                                            {{ TYPE_LABEL[c.type] }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge :status="c.is_taxable ? 'active' : 'inactive'" />
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge :status="c.is_fixed ? 'active' : 'inactive'" />
                                    </TableCell>
                                    <TableCell class="text-muted-foreground max-w-[200px] truncate text-xs font-mono">
                                        {{ c.formula ?? '—' }}
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge :status="c.is_active ? 'active' : 'inactive'" />
                                    </TableCell>
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
                                <TableRow v-if="components.length === 0">
                                    <TableCell colspan="8" class="text-muted-foreground h-24 text-center">
                                        Belum ada komponen gaji.
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
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'Edit' : 'Tambah' }} Komponen Gaji</DialogTitle>
                    <DialogDescription>Konfigurasi komponen earning, deduction, atau benefit.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Nama *</Label>
                            <Input v-model="form.name" placeholder="Gaji Pokok..." />
                            <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Kode *</Label>
                            <Input v-model="form.code" placeholder="GAPOK" class="uppercase font-mono" maxlength="20" />
                            <p v-if="form.errors.code" class="text-destructive text-xs">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <Label>Tipe *</Label>
                            <Select v-model="form.type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="earning">Pendapatan</SelectItem>
                                    <SelectItem value="deduction">Potongan</SelectItem>
                                    <SelectItem value="benefit">Benefit</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Sort Order</Label>
                            <Input v-model.number="form.sort_order" type="number" min="0" />
                        </div>
                    </div>

                    <div v-if="!form.is_fixed" class="space-y-2">
                        <Label>Formula</Label>
                        <Input v-model="form.formula" placeholder="gapok * 0.01" class="font-mono text-sm" />
                        <p class="text-muted-foreground text-xs">Variabel: gapok, overtime_minutes, absent_days</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p class="text-sm font-medium">Fixed Amount</p>
                                <p class="text-muted-foreground text-xs">Amount tetap per karyawan</p>
                            </div>
                            <Switch v-model:checked="form.is_fixed" />
                        </div>
                        <div class="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p class="text-sm font-medium">Kena Pajak (PPh 21)</p>
                            </div>
                            <Switch v-model:checked="form.is_taxable" />
                        </div>
                        <div class="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p class="text-sm font-medium">Aktif</p>
                            </div>
                            <Switch v-model:checked="form.is_active" />
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
            title="Hapus Komponen"
            description="Komponen yang sudah digunakan dalam payroll tidak dapat dihapus."
        />
    </AppLayout>
</template>
