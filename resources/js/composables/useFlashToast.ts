import { onMounted, onUnmounted } from 'vue';
import { toast } from 'vue-sonner';

export function useFlashToast() {
    const handler = (event: Event) => {
        const page = (event as CustomEvent).detail?.page;
        const flash = page?.props?.flash;
        if (!flash) return;
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.warning) toast.warning(flash.warning);
        if (flash.info) toast.info(flash.info);
    };

    onMounted(() => {
        document.addEventListener('inertia:success', handler);
    });

    onUnmounted(() => {
        document.removeEventListener('inertia:success', handler);
    });
}
