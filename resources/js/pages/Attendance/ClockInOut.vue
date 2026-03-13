<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LogIn, LogOut, MapPin, AlertCircle, CheckCircle2, Clock } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SelfieCapture from '@/components/SelfieCapture.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useGeolocation } from '@/composables/useGeolocation';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

type AttendanceSummary = {
    status: string;
    late_minutes: number;
    overtime_minutes: number;
    work_duration_minutes: number;
};

type TodayData = {
    clock_in: string | null;
    clock_out: string | null;
    summary: AttendanceSummary | null;
};

const props = defineProps<{
    employee: { id: number; full_name: string; employee_code: string } | null;
    today: TodayData | null;
    officeLat: number;
    officeLng: number;
    radiusMeters: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clock In/Out', href: '/attendance/clock' },
];

const { getCurrentPosition, checkRadius, checking } = useGeolocation();

const currentTime = ref(new Date());
const geoError = ref<string | null>(null);
const capturedSelfie = ref<File | null>(null);
const geoPosition = ref<{ latitude: number; longitude: number } | null>(null);
const distanceMeters = ref<number | null>(null);

let clockInterval: ReturnType<typeof setInterval>;
onMounted(() => { clockInterval = setInterval(() => { currentTime.value = new Date(); }, 1000); });
onUnmounted(() => clearInterval(clockInterval));

const timeStr = computed(() => currentTime.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
const dateStr = computed(() => currentTime.value.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));

const hasClockIn = computed(() => !!props.today?.clock_in);
const hasClockOut = computed(() => !!props.today?.clock_out);
const nextAction = computed<'IN' | 'OUT'>(() => (hasClockIn.value && !hasClockOut.value ? 'OUT' : 'IN'));

const form = useForm({ event_type: 'IN', latitude: 0, longitude: 0, selfie: null as File | null });

const checkLocation = async () => {
    geoError.value = null;
    try {
        const pos = await getCurrentPosition();
        geoPosition.value = pos;
        const { withinRadius, distance } = checkRadius(pos.latitude, pos.longitude, props.officeLat, props.officeLng, props.radiusMeters);
        distanceMeters.value = distance;
        if (!withinRadius) {
            geoError.value = `Anda berada ${distance}m dari kantor (maks. ${props.radiusMeters}m). Clock-in tidak diizinkan.`;
        }
    } catch (e: unknown) {
        geoError.value = e instanceof Error ? e.message : 'Gagal mendapatkan lokasi.';
    }
};

const onSelfie = (file: File) => { capturedSelfie.value = file; };
const onSelfieError = (msg: string) => { geoError.value = msg; };

const isReadyToSubmit = computed(() =>
    capturedSelfie.value !== null &&
    geoPosition.value !== null &&
    !geoError.value
);

const submit = () => {
    if (!geoPosition.value || !capturedSelfie.value) return;

    form.event_type = nextAction.value;
    form.latitude   = geoPosition.value.latitude;
    form.longitude  = geoPosition.value.longitude;
    form.selfie     = capturedSelfie.value;

    form.post('/attendance/clock', {
        forceFormData: true,
        onSuccess: () => {
            capturedSelfie.value = null;
            geoPosition.value    = null;
            distanceMeters.value = null;
        },
    });
};

const formatTime = (iso: string | null) =>
    iso ? new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';
</script>

<template>
    <Head title="Clock In/Out" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6 max-w-2xl">

            <!-- Live clock -->
            <Card>
                <CardContent class="pt-6 text-center">
                    <p class="text-5xl font-bold tabular-nums tracking-tight">{{ timeStr }}</p>
                    <p class="mt-1 text-muted-foreground capitalize">{{ dateStr }}</p>
                </CardContent>
            </Card>

            <!-- No employee record -->
            <Alert v-if="!employee" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>Akun Anda belum terhubung ke data karyawan. Hubungi HR.</AlertDescription>
            </Alert>

            <template v-else>
                <!-- Today's status -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">Status Hari Ini</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Clock In</p>
                                <p class="font-semibold">{{ formatTime(today?.clock_in ?? null) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Clock Out</p>
                                <p class="font-semibold">{{ formatTime(today?.clock_out ?? null) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Status</p>
                                <StatusBadge v-if="today?.summary" :status="today.summary.status" />
                                <span v-else class="text-sm text-muted-foreground">-</span>
                            </div>
                        </div>
                        <div v-if="today?.summary" class="mt-3 grid grid-cols-3 gap-4 text-center text-sm">
                            <div>
                                <p class="text-xs text-muted-foreground">Terlambat</p>
                                <p>{{ today.summary.late_minutes }} mnt</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">Lembur</p>
                                <p>{{ today.summary.overtime_minutes }} mnt</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">Durasi</p>
                                <p>{{ Math.floor((today.summary.work_duration_minutes ?? 0) / 60) }}j {{ (today.summary.work_duration_minutes ?? 0) % 60 }}m</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Already clocked out -->
                <Alert v-if="hasClockOut">
                    <CheckCircle2 class="h-4 w-4" />
                    <AlertDescription>Anda sudah melakukan clock out hari ini. Sampai jumpa besok!</AlertDescription>
                </Alert>

                <!-- Clock action -->
                <Card v-else>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Clock class="h-4 w-4" />
                            {{ nextAction === 'IN' ? 'Clock In' : 'Clock Out' }}
                        </CardTitle>
                        <CardDescription>
                            {{ nextAction === 'IN' ? 'Verifikasi lokasi dan selfie untuk absensi masuk.' : 'Verifikasi lokasi dan selfie untuk absensi pulang.' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">

                        <!-- Step 1: Location -->
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Langkah 1 — Verifikasi Lokasi</p>
                            <Button type="button" variant="outline" :disabled="checking" @click="checkLocation" class="flex items-center gap-2">
                                <MapPin class="h-4 w-4" />
                                {{ checking ? 'Mencari lokasi...' : (geoPosition ? 'Lokasi OK ✓' : 'Cek Lokasi') }}
                            </Button>
                            <p v-if="distanceMeters !== null && !geoError" class="text-sm text-emerald-600">
                                ✓ Jarak {{ distanceMeters }}m dari kantor (maks. {{ radiusMeters }}m)
                            </p>
                            <Alert v-if="geoError" variant="destructive" class="py-2">
                                <AlertCircle class="h-4 w-4" />
                                <AlertDescription class="text-xs">{{ geoError }}</AlertDescription>
                            </Alert>
                        </div>

                        <!-- Step 2: Selfie -->
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Langkah 2 — Ambil Selfie</p>
                            <SelfieCapture @captured="onSelfie" @error="onSelfieError" />
                            <p v-if="capturedSelfie" class="text-sm text-emerald-600">✓ Selfie tersimpan</p>
                        </div>

                        <!-- Submit -->
                        <Button
                            @click="submit"
                            :disabled="!isReadyToSubmit || form.processing"
                            class="w-full"
                            :class="nextAction === 'OUT' ? 'bg-amber-600 hover:bg-amber-700' : ''"
                        >
                            <LogIn v-if="nextAction === 'IN'" class="mr-2 h-4 w-4" />
                            <LogOut v-else class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : (nextAction === 'IN' ? 'Clock In Sekarang' : 'Clock Out Sekarang') }}
                        </Button>
                    </CardContent>
                </Card>
            </template>
        </div>
    </AppLayout>
</template>
