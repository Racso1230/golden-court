<script setup lang="ts">
/**
 * A slim notice that Golden Court is a concept project, not a real service.
 * The layouts show it only when the server says so (the `conceptBanner`
 * shared prop, from GOLDEN_COURT_CONCEPT_BANNER), which the public portfolio
 * deployment switches on. It is plain text and one link, so it renders the
 * same on the server and in the browser.
 */
</script>

<template>
    <div
        role="note"
        aria-label="About this site"
        class="bg-foreground text-background px-4 py-2 text-center text-sm"
        data-test="concept-banner"
    >
        <strong class="font-semibold">This is a concept.</strong>
        Golden Court is a portfolio project by
        <a
            href="https://ojfdigital.co.uk"
            class="underline underline-offset-4 hover:no-underline"
            >OJF Digital</a
        >. Its venues and reviews are fictional.
    </div>
</template>
