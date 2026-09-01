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
        class="font-display text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
      >
        Welcome, {{ username }}.
      </h1>

      <p class="text-lg leading-relaxed text-slate-700 dark:text-gray-300">
        We will generate a personalized, weekly meal plan tailored to your
        specific metabolic needs, dietary preferences, and fitness goals.
      </p>

      <p class="text-lg leading-relaxed text-slate-700 dark:text-gray-300">
        To ensure the baseline calculations are accurate, we need to collect a
        few physical metrics and learn about your routine.
      </p>
    </div>

    <UAlert
      icon="i-heroicons-exclamation-triangle"
      color="warning"
      variant="soft"
      title="Important Notice"
      description="Our recipes are aggregated from open-source databases and have not been medically verified. Please independently verify all ingredients if you have food allergies, sensitivities, or specific medical conditions."
      class="text-left shadow-sm ring-1 ring-amber-500/30 dark:ring-amber-400/30"
      :ui="{
        title: 'text-amber-900 dark:text-amber-400 font-bold',
        description: 'text-amber-800 dark:text-amber-200/90',
        icon: 'text-amber-600 dark:text-amber-400',
      }"
    />
  </div>
</template>
