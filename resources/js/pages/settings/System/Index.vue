<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Settings2, Save } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';

type Setting = {
    id: number; key: string; value: string; type: string; group: string; label: string; description?: string;
};

const props = defineProps<{
    settings: Record<string, Setting[]>;
    groups: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengaturan', href: '#' },
    { title: 'Sistem', href: '#' },
];

const groupLabels: Record<string, string> = {
    attendance: '🕑 Absensi & Geolokasi',
    payroll:    '💰 Payroll',
    company:    '🏢 Informasi Perusahaan',
    general:    '⚙️ Umum',
};

// Local mutable copy of values
const localValues = ref<Record<string, string>>(
    Object.values(props.settings).flat().reduce((acc, s) => {
        acc[s.key] = s.value;
        return acc;
    }, {} as Record<string, string>)
);

const form = useForm({});

function save(group: string) {
    const groupSettings = props.settings[group] ?? [];
    const payload = groupSettings.map(s => ({ key: s.key, value: localValues.value[s.key] }));

    form.transform(() => ({ settings: payload }))
        .put('/settings/system', {
            preserveScroll: true,
        });
}
</script>

<template>
    <Head title="Pengaturan Sistem" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">

            <!-- Header -->
            <div class="flex items-center gap-3">
                <Settings2 class="h-6 w-6 text-primary" />
                <div>
                    <h1 class="text-xl font-bold">Pengaturan Sistem</h1>
                    <p class="text-sm text-muted-foreground">Konfigurasi parameter sistem OrcaHR</p>
                </div>
            </div>

            <!-- Per-group cards -->
            <div v-for="group in groups" :key="group">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-base">{{ groupLabels[group] ?? group }}</CardTitle>
                        <Button size="sm" :disabled="form.processing" @click="save(group)">
                            <Save class="mr-2 h-3.5 w-3.5" /> Simpan
                        </Button>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-for="s in settings[group]" :key="s.key" class="grid grid-cols-3 items-center gap-4">
                            <div class="col-span-1">
                                <Label class="font-medium text-sm">{{ s.label }}</Label>
                                <p v-if="s.description" class="text-xs text-muted-foreground mt-0.5">{{ s.description }}</p>
                            </div>
                            <div class="col-span-2">
                                <Input
                                    v-if="s.type !== 'boolean'"
                                    v-model="localValues[s.key]"
                                    :type="['integer','decimal'].includes(s.type) ? 'number' : 'text'"
                                    :step="s.type === 'decimal' ? '0.000001' : '1'"
                                    class="font-mono"
                                />
                                <label v-else class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="localValues[s.key]" class="h-4 w-4 rounded" />
                                    <span class="text-sm">Aktif</span>
                                </label>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
