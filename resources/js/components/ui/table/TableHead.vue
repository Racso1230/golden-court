<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
    <th
        scope="col"
        :class="
            cn(
                'text-foreground h-10 px-2 text-left align-middle font-medium whitespace-nowrap',
                props.class,
            )
        "
    >
        <slot />
    </th>
</template>
