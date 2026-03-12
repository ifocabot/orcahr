<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useFlashToast } from '@/composables/useFlashToast';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

useFlashToast();

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <Teleport to="body">
        <Toaster position="top-right" :rich-colors="true" />
    </Teleport>
</template>
