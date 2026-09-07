<script setup lang="ts">
import bestProductIcon from '../../images/Auth/Badges/best-product.svg';
import fitnessIcon from '../../images/Auth/Badges/fitness.svg';
import ecoIcon from '../../images/Auth/Badges/eco.svg';
import dietIcon from '../../images/Auth/Badges/diet.svg';
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
      class="bg-surface-container-low z-10 flex w-full shrink-0 grow flex-col items-center shadow-[0px_4px_20px_rgba(0,0,0,0.04)] md:w-1/2"
    >
      <div class="mx-auto my-auto w-full max-w-6xl px-6 lg:px-8">
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

      <footer
        class="border-surface-variant/30 bg-surface-container-lowest/30 mt-auto w-full border-t px-2 py-4 sm:px-6 sm:py-6"
      >
        <div
          class="mx-auto flex w-full max-w-2xl flex-row items-center justify-between gap-1 sm:gap-3"
        >
          <UBadge
            color="gray"
            variant="subtle"
            class="flex flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 transition-all sm:flex-row sm:gap-2 sm:px-3 sm:py-2.5"
          >
            <img
              :src="bestProductIcon"
              alt="Top Rated"
              class="h-6 w-6 shrink-0 drop-shadow-sm sm:h-7 sm:w-7 md:h-8 md:w-8 lg:h-10 lg:w-10 xl:h-12 xl:w-12"
            />
            <span
              class="text-center text-[10px] leading-tight font-semibold tracking-wider uppercase sm:text-xs lg:text-sm"
              >Top Rated</span
            >
          </UBadge>

          <UBadge
            color="gray"
            variant="subtle"
            class="flex flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 transition-all sm:flex-row sm:gap-2 sm:px-3 sm:py-2.5"
          >
            <img
              :src="fitnessIcon"
              alt="Goal Support"
              class="ssm:h-7 h-6 w-6 shrink-0 drop-shadow-sm sm:w-7 md:h-8 md:w-8 lg:h-10 lg:w-10 xl:h-12 xl:w-12"
            />
            <span
              class="text-center text-[10px] leading-tight font-semibold tracking-wider uppercase sm:text-xs lg:text-sm"
              >Fitness Goals</span
            >
          </UBadge>

          <UBadge
            color="gray"
            variant="subtle"
            class="flex flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 transition-all sm:flex-row sm:gap-2 sm:px-3 sm:py-2.5"
          >
            <img
              :src="ecoIcon"
              alt="Zero Waste"
              class="h-6 w-6 shrink-0 drop-shadow-sm sm:h-7 sm:w-7 md:h-8 md:w-8 lg:h-10 lg:w-10 xl:h-12 xl:w-12"
            />
            <span
              class="text-center text-[10px] leading-tight font-semibold tracking-wider uppercase sm:text-xs lg:text-sm"
              >Zero Waste</span
            >
          </UBadge>

          <UBadge
            color="gray"
            variant="subtle"
            class="flex flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 transition-all sm:flex-row sm:gap-2 sm:px-3 sm:py-2.5"
          >
            <img
              :src="dietIcon"
              alt="All Diets"
              class="h-6 w-6 shrink-0 drop-shadow-sm sm:h-7 sm:w-7 md:h-8 md:w-8 lg:h-10 lg:w-10 xl:h-12 xl:w-12"
            />
            <span
              class="text-center text-[10px] leading-tight font-semibold tracking-wider uppercase sm:text-xs lg:text-sm"
              >All Diets</span
            >
          </UBadge>
        </div>
      </footer>
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
