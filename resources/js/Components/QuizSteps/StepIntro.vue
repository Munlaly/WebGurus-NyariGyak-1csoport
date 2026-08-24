<script setup lang="ts">
defineProps<{
  username: string;
}>();
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-6">
      <h1
        class="font-display text-on-surface text-4xl font-bold tracking-tight"
      >
        Welcome, {{ username }}.
      </h1>

      <p class="text-on-surface-variant text-lg leading-relaxed">
        We will generate a personalized, weekly meal plan tailored to your
        specific metabolic needs, dietary preferences, and fitness goals.
      </p>

      <p class="text-on-surface-variant text-lg leading-relaxed">
        To ensure the baseline calculations are accurate, we need to collect a
        few physical metrics and learn about your routine.
      </p>
    </div>

    <UAlert
      icon="i-heroicons-exclamation-triangle"
      color="amber"
      variant="subtle"
      title="Important Notice"
      description="Our recipes are aggregated from open-source databases and have not been medically verified. Please independently verify all ingredients if you have food allergies, sensitivities, or specific medical conditions."
      class="text-left shadow-sm"
    />
  </div>
</template>
