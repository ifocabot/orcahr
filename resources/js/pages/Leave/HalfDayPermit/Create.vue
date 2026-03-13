<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Clock3, Info } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuti', href: '#' },
    { title: 'Izin ½ Hari', href: '/leave/half-day/create' },
];

const form = useForm({
    work_date: '',
    session: 'AM',
    reason: '',
});

function submit() {
    form.post('/attendance/exceptions/half-day');
}

const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <Head title="Izin Setengah Hari" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock3 class="h-5 w-5" /> Izin Setengah Hari
                        </CardTitle>
                        <CardDescription>
                            Durasi minimum izin adalah ½ dari jadwal shift aktif Anda.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Info -->
                            <Alert>
                                <Info class="h-4 w-4" />
                                <AlertDescription>
                                    Izin setengah hari <strong>tidak mengurangi saldo cuti</strong>. Pengajuan masih memerlukan persetujuan atasan.
                                </AlertDescription>
                            </Alert>

                            <!-- Date -->
                            <div class="space-y-1.5">
                                <Label>Tanggal</Label>
                                <Input type="date" v-model="form.work_date" :min="today" />
                                <p v-if="form.errors.work_date" class="text-destructive text-xs">{{ form.errors.work_date }}</p>
                            </div>

                            <!-- Session -->
                            <div class="space-y-1.5">
                                <Label>Sesi Izin</Label>
                                <div class="flex gap-6">
                                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                                        <input type="radio" v-model="form.session" value="AM" class="accent-primary" />
                                        Pagi (AM)
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                                        <input type="radio" v-model="form.session" value="PM" class="accent-primary" />
                                        Siang (PM)
                                    </label>
                                </div>
                                <p v-if="form.errors.session" class="text-destructive text-xs">{{ form.errors.session }}</p>
                            </div>

                            <!-- Reason -->
                            <div class="space-y-1.5">
                                <Label>Alasan</Label>
                                <textarea
                                    v-model="form.reason"
                                    placeholder="Tuliskan alasan izin..."
                                    rows="3"
                                    class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <p v-if="form.errors.reason" class="text-destructive text-xs">{{ form.errors.reason }}</p>
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <Button type="button" variant="outline" @click="router.visit('/leave/requests')">Batal</Button>
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Mengirim...' : 'Ajukan Izin' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
