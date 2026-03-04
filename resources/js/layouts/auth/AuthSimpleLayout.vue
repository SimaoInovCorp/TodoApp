<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <div
                        class="flex flex-col items-center gap-2 font-medium select-none"
                    >
                        <span
                            class="animate-inovcorp-pulse font-serif text-2xl tracking-tight text-foreground"
                            style="
                                text-shadow:
                                    1px 1px 6px #b0b6be,
                                    0 0 2px #e0e0e0;
                                letter-spacing: 0.04em;
                            "
                        >
                            ino<b class="font-bold">V</b>corp
                        </span>
                    </div>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes inovcorp-pulse {
    0%,
    100% {
        transform: scale(1);
        text-shadow:
            1px 1px 6px #b0b6be,
            0 0 2px #e0e0e0;
    }
    50% {
        transform: scale(1.06);
        text-shadow:
            0 0 16px #b0b6be,
            0 0 8px #e0e0e0;
    }
}
.animate-inovcorp-pulse {
    animation: inovcorp-pulse 2.2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    display: inline-block;
}
</style>
