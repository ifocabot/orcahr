<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    status: string;
    approveUrl: string;
    rejectUrl: string;
}>();

const emit = defineEmits<{ approved: []; rejected: [] }>();

const showRejectInput = ref(false);
const rejectReason = ref('');
const loading = ref<'approve' | 'reject' | null>(null);

const approve = () => {
    loading.value = 'approve';
    router.put(props.approveUrl, {}, {
        onSuccess: () => { emit('approved'); },
        onFinish: () => { loading.value = null; },
    });
};

const confirmReject = () => {
    if (!rejectReason.value.trim()) return;
    loading.value = 'reject';
    router.put(props.rejectUrl, { reason: rejectReason.value }, {
        onSuccess: () => { showRejectInput.value = false; emit('rejected'); },
        onFinish: () => { loading.value = null; },
    });
};
</script>

<template>
    <div v-if="status === 'pending'" class="flex flex-col gap-1">
        <div class="flex gap-1">
            <Button
                variant="outline" size="sm"
                class="h-7 border-emerald-500 text-emerald-600 hover:bg-emerald-50"
                :disabled="loading !== null"
                @click="approve"
            >
                <Check class="h-3.5 w-3.5 mr-1" />
                {{ loading === 'approve' ? '...' : 'Setuju' }}
            </Button>
            <Button
                variant="outline" size="sm"
                class="h-7 border-red-400 text-red-600 hover:bg-red-50"
                :disabled="loading !== null"
                @click="showRejectInput = !showRejectInput"
            >
                <X class="h-3.5 w-3.5 mr-1" /> Tolak
            </Button>
        </div>
        <div v-if="showRejectInput" class="flex gap-1 mt-1">
            <Input v-model="rejectReason" placeholder="Alasan penolakan..." class="h-7 text-xs" />
            <Button size="sm" variant="destructive" class="h-7" @click="confirmReject" :disabled="!rejectReason.trim()">OK</Button>
        </div>
    </div>
    <span v-else class="text-xs text-muted-foreground">—</span>
</template>
