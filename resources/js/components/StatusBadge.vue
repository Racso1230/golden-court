<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { StatusTone } from '@/lib/status';

const props = defineProps<{ tone: StatusTone }>();

const variant = computed(() =>
    props.tone === 'neutral' ? 'secondary' : props.tone,
);
</script>

<template>
    <Badge :variant="variant"><slot /></Badge>
</template>
