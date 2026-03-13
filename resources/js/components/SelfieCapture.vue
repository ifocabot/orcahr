<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { Camera, X, RotateCcw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const emit = defineEmits<{
    captured: [file: File];
    error: [message: string];
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const previewUrl = ref<string | null>(null);
const stream = ref<MediaStream | null>(null);
const isReady = ref(false);
const isCapturing = ref(false);

const startCamera = async () => {
    try {
        isCapturing.value = true;
        stream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
            videoRef.value.play();
            isReady.value = true;
        }
    } catch {
        emit('error', 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
        isCapturing.value = false;
    }
};

const capture = () => {
    if (!videoRef.value || !canvasRef.value) return;

    const video = videoRef.value;
    const canvas = canvasRef.value;
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d')!.drawImage(video, 0, 0);

    previewUrl.value = canvas.toDataURL('image/jpeg', 0.85);
    stopStream();

    canvas.toBlob(
        (blob) => {
            if (!blob) return;
            const file = new File([blob], `selfie-${Date.now()}.jpg`, { type: 'image/jpeg' });
            emit('captured', file);
        },
        'image/jpeg',
        0.85,
    );
};

const retake = () => {
    previewUrl.value = null;
    isReady.value = false;
    startCamera();
};

const stopStream = () => {
    stream.value?.getTracks().forEach((t) => t.stop());
    stream.value = null;
    isReady.value = false;
};

const cancel = () => {
    stopStream();
    previewUrl.value = null;
    isCapturing.value = false;
};

onUnmounted(() => stopStream());
</script>

<template>
    <div class="space-y-3">
        <!-- Preview after capture -->
        <div v-if="previewUrl" class="relative">
            <img :src="previewUrl" alt="Selfie" class="w-full max-w-xs rounded-lg border object-cover" />
            <Button
                type="button" variant="outline" size="sm"
                class="mt-2 flex items-center gap-2"
                @click="retake"
            >
                <RotateCcw class="h-4 w-4" /> Ambil Ulang
            </Button>
        </div>

        <!-- Camera active -->
        <div v-else-if="isCapturing" class="space-y-2">
            <div class="relative overflow-hidden rounded-lg border bg-black max-w-xs">
                <video ref="videoRef" class="w-full" autoplay muted playsinline />
            </div>
            <canvas ref="canvasRef" class="hidden" />
            <div class="flex gap-2">
                <Button type="button" @click="capture" :disabled="!isReady" class="flex items-center gap-2">
                    <Camera class="h-4 w-4" /> Ambil Foto
                </Button>
                <Button type="button" variant="ghost" @click="cancel" class="flex items-center gap-2">
                    <X class="h-4 w-4" /> Batal
                </Button>
            </div>
        </div>

        <!-- Initial state -->
        <Button v-else type="button" variant="outline" @click="startCamera" class="flex items-center gap-2">
            <Camera class="h-4 w-4" /> Buka Kamera
        </Button>
    </div>
</template>
