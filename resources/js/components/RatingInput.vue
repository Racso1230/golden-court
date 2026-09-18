<script setup lang="ts">
import { computed, ref } from 'vue';
import { RATINGS, type Rating } from '@/types/rating';

/**
 * A 1–5 selector that behaves like a native radio group: arrow keys move
 * the selection, Home/End jump to the ends, and each option is labelled.
 */
const props = withDefaults(
    defineProps<{
        label: string;
        hint?: string;
        error?: string;
        disabled?: boolean;
    }>(),
    { disabled: false },
);

const model = defineModel<Rating | null>({ default: null });

const uid = `rating-${Math.random().toString(36).slice(2, 8)}`;
const buttons = ref<HTMLButtonElement[]>([]);

const descriptionIds = computed(() =>
    [props.hint ? `${uid}-hint` : null, props.error ? `${uid}-error` : null]
        .filter((id): id is string => id !== null)
        .join(' '),
);

function select(value: Rating): void {
    if (!props.disabled) {
        model.value = value;
    }
}

function focusIndex(index: number): void {
    const target = buttons.value[index];

    if (target) {
        target.focus();
    }
}

function onKeydown(event: KeyboardEvent, index: number): void {
    const last = RATINGS.length - 1;
    const moves: Record<string, number> = {
        ArrowRight: Math.min(last, index + 1),
        ArrowUp: Math.min(last, index + 1),
        ArrowLeft: Math.max(0, index - 1),
        ArrowDown: Math.max(0, index - 1),
        Home: 0,
        End: last,
    };

    const next = moves[event.key];

    if (next === undefined) {
        return;
    }

    event.preventDefault();
    const rating = RATINGS[next];

    if (rating !== undefined) {
        select(rating);
        focusIndex(next);
    }
}

function tabIndexFor(value: Rating): number {
    // Only one option is in the tab order, like a native radio group.
    if (model.value === null) {
        return value === 1 ? 0 : -1;
    }

    return model.value === value ? 0 : -1;
}
</script>

<template>
    <div class="grid gap-1.5">
        <div
            :id="`${uid}-label`"
            class="text-sm leading-none font-medium select-none"
        >
            {{ label }}
        </div>
        <p
            v-if="hint"
            :id="`${uid}-hint`"
            class="text-muted-foreground text-xs"
        >
            {{ hint }}
        </p>
        <div
            role="radiogroup"
            :aria-labelledby="`${uid}-label`"
            :aria-describedby="descriptionIds || undefined"
            :aria-invalid="error ? 'true' : undefined"
            class="flex gap-1"
        >
            <button
                v-for="(value, index) in RATINGS"
                :key="value"
                ref="buttons"
                type="button"
                role="radio"
                :aria-checked="model === value"
                :aria-label="`${value} out of 5`"
                :tabindex="tabIndexFor(value)"
                :disabled="disabled"
                class="focus-visible:ring-ring/50 flex size-10 items-center justify-center rounded-md border text-lg transition-colors outline-none focus-visible:ring-[3px] disabled:opacity-50"
                :class="
                    model !== null && value <= model
                        ? 'border-amber-500 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'
                        : 'text-muted-foreground hover:bg-accent'
                "
                @click="select(value)"
                @keydown="onKeydown($event, index)"
            >
                <span aria-hidden="true">★</span>
            </button>
            <span
                class="text-muted-foreground ml-2 self-center text-sm tabular-nums"
                aria-live="polite"
            >
                {{ model === null ? 'Not rated' : `${model} / 5` }}
            </span>
        </div>
        <p
            v-if="error"
            :id="`${uid}-error`"
            class="text-destructive text-sm"
            role="alert"
        >
            {{ error }}
        </p>
    </div>
</template>
