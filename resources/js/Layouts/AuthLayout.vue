<script setup lang="ts">
defineProps<{
  heading: string;
  subheading?: string;
  imageSrc: string;
  imageAlt: string;
}>();
</script>

<template>
  <div
    class="bg-background text-on-surface font-body-md flex min-h-screen antialiased"
  >
    <!-- Left Side: Form Area -->
    <main
      class="bg-surface-container-low z-10 flex w-full shrink-0 grow flex-col justify-center shadow-[0px_4px_20px_rgba(0,0,0,0.04)] md:w-1/2"
    >
      <div class="mx-auto w-full max-w-md px-6 lg:px-8">
        <header class="mb-8">
          <slot name="logo">
            <h2
              class="font-display text-primary mb-2 text-[24px] font-bold tracking-tight"
            >
              Smart &amp; ZeroWaste
            </h2>
          </slot>

          <h1 class="text-on-surface font-headline-lg mb-2 text-3xl font-bold">
            {{ heading }}
          </h1>
          <p v-if="subheading" class="text-on-surface-variant font-body-md">
            {{ subheading }}
          </p>
        </header>

        <slot />
      </div>
    </main>

    <!-- Right Side: Hero Image -->
    <aside
      class="bg-surface-variant relative hidden w-1/2 overflow-hidden md:block"
    >
      <img
        :alt="imageAlt"
        class="absolute inset-0 h-full w-full object-cover"
        :src="imageSrc"
      />
      <!-- The gradient overlay -->
      <div
        class="from-on-surface/5 absolute inset-0 bg-linear-to-tr to-transparent mix-blend-multiply"
      />
    </aside>
  </div>
</template>
