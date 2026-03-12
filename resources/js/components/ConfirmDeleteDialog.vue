<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel,
    AlertDialogContent, AlertDialogDescription, AlertDialogFooter,
    AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    deleteUrl: string;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const proceed = () => {
    router.delete(props.deleteUrl, {
        onSuccess: () => emit('update:open', false),
    });
};
</script>

<template>
    <AlertDialog :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title ?? 'Hapus Data' }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ description ?? 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.' }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive text-destructive-foreground hover:bg-destructive/90" @click="proceed">
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
