<script setup lang="ts">
import { Trophy } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';

defineProps<{ city?: string }>();
</script>

<template>
    <Badge
        variant="outline"
        class="border-gold-300 bg-gold-50 text-gold-800"
        :title="
            city ? `Top-rated court in ${city}` : 'Top-rated court in its city'
        "
    >
        <Trophy aria-hidden="true" />
        Golden Court<template v-if="city"> of {{ city }}</template>
    </Badge>
</template>
