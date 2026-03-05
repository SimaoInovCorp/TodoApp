<script setup lang="ts">
/**
 * WelcomeHero.vue
 * A reusable hero/intro block for welcome or landing pages.
 * Props: title (string), description (string), customClass (optional string)
 */
defineProps<{
  title: string;
  description: string;
  customClass?: string;
}>();
</script>

<template>
  <section :class="['welcome-hero', customClass]">
    <h1 class="text-shadow mb-1 text-2xl font-bold tracking-tight sm:text-3xl">
      {{ title }}
    </h1>
    <p class="text-shade-700 text-shadow mb-2 text-sm">
      <span v-html="description" />
    </p>
  </section>
</template>

<style scoped>
.welcome-hero {
  /* Layout and background can be customized by parent */
}
.text-shadow {
  text-shadow:
    0 4px 24px rgba(0,0,0,0.55),
    0 1px 0 #fff,
    0 0px 2px #000,
    0 2px 8px rgba(0,0,0,0.35);
}
</style>
