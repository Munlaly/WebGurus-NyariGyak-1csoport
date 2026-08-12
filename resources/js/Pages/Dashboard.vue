<script setup lang="ts">
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
</script>

<template>
  <AuthenticatedLayout>
    <div
      class="border-outline-variant flex h-64 items-center justify-center rounded-xl border-2 border-dashed"
    >
      <div class="text-on-surface-variant text-center">
        <span class="material-symbols-outlined mb-2 text-4xl">celebration</span>
        <h1 class="font-headline-md text-headline-md font-bold">
          Dashboard Layout Active
        </h1>
        <p class="font-body-md text-body-md mt-2">
          Your sidebar and topbar are routing correctly.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
