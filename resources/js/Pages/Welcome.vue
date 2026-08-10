<template>
  <div
    class="flex min-h-screen items-center justify-center bg-gray-50 p-4 dark:bg-gray-900"
  >
    <UCard class="w-full max-w-md">
      <template #header>
        <h1 class="text-primary text-2xl font-bold">Nuxt UI is Online 🚀</h1>
      </template>

      <p class="mb-4 text-gray-600 dark:text-gray-300">
        If this card has a border, shadow, and the button below is styled
        perfectly, the Vite auto-imports and Tailwind configuration are
        flawless.
      </p>

      <UButton color="primary" variant="solid" block>
        System Fully Operational
      </UButton>
    </UCard>
  </div>
</template>

<script setup>
// No imports needed! The Nuxt UI Vite plugin handles it automatically.
</script>

<style scoped>
.container {
  text-align: center;
  margin-top: 50px;
  font-family: sans-serif;
}
h1 {
  color: #42b883;
}
</style>
