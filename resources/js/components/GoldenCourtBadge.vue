<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

defineProps<{ city?: string }>();
</script>

<template>
    <Badge
        variant="outline"
        class="border-amber-400 bg-amber-50 text-amber-800"
        :title="
            city ? `Top-rated court in ${city}` : 'Top-rated court in its city'
        "
    >
        <span aria-hidden="true">🏆</span>
        Golden Court<template v-if="city"> of {{ city }}</template>
    </Badge>
</template>
