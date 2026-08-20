<script setup lang="ts">
import { z } from 'zod';
import { useForm, Link } from '@inertiajs/vue3';

// Define client side validation
const schema = z.object({
  username: z.string().min(2, 'Name must be at least 2 characters'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
});

const form = useForm({
  username: '',
  password: '',
});

function onSubmit() {
  form.post(route('login'), {
    onSuccess: () => form.reset('password'),
  });
}
</script>

<template>
  <div class="flex min-h-screen w-full">
    <!-- Left Side: Form Area -->
    <main
      class="bg-surface-container-lowest px-margin-mobile md:px-margin-desktop relative z-10 flex w-full flex-col justify-center py-12 sm:px-12 md:w-1/2 lg:px-32"
    >
      <div class="mx-auto flex w-full max-w-105 flex-col gap-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-2">
          <h2 class="font-display text-display text-primary">
            Smart &amp; ZeroWaste
          </h2>
          <h1 class="font-headline-lg text-headline-lg text-on-surface mt-4">
            Welcome back, Planner
          </h1>
          <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Log in to manage your inventory and weekly meals.
          </p>
        </header>

        <UForm
          :schema="schema"
          :state="form"
          class="space-y-4"
          @submit="onSubmit"
        >
          <UFormField
            label="Full Name"
            name="username"
            :error="form.errors.username"
          >
            <UInput v-model="form.username" />
          </UFormField>

          <UFormField
            label="Password"
            name="password"
            :error="form.errors.password"
          >
            <UInput v-model="form.password" type="password" />
          </UFormField>
          <UButton type="submit" :loading="form.processing">
            Log In
            <span class="material-symbols-outlined text-[18px]"
              >arrow_forward</span
            >
          </UButton>
        </UForm>

        <!-- Footer Section -->
        <div class="mt-4 text-center">
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            Don't have an account?
            <Link
              class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
              :href="route('register')"
            >
              Sign up
            </Link>
          </p>
        </div>
      </div>
    </main>
    <!-- Right Side: Hero Image -->
    <aside class="bg-surface-container relative hidden md:block md:w-1/2">
      <div class="absolute inset-0 h-full w-full">
        <img
          alt="Fresh vegetables and meal prep containers"
          class="h-full w-full object-cover"
          data-alt="A top-down, high-quality professional food photography shot of a modern, clean kitchen counter. The scene features an organized arrangement of fresh, vibrant green vegetables like kale, broccoli, and spinach, alongside sleek, clear glass meal prep containers filled with meticulously portioned healthy ingredients. The lighting is bright, natural, and soft, creating a fresh, light-mode aesthetic that feels airy and breathable. The composition relies on heavy whitespace and a subtle color palette of sage greens and crisp whites, embodying a modern minimalist, eco-conscious lifestyle."
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
        />
        <!-- Subtle Gradient Overlay to ensure image doesn't wash out completely if too bright -->
        <div
          class="from-on-surface/5 absolute inset-0 bg-linear-to-tr to-transparent mix-blend-multiply"
        ></div>
      </div>
      <!-- Optional: Atmospheric Floating Badge -->
      <div
        class="bg-surface-container-lowest/90 absolute right-12 bottom-12 max-w-60 rounded-xl p-6 shadow-[0px_10px_30px_rgba(0,0,0,0.08)] backdrop-blur-md"
      >
        <div class="mb-2 flex items-center gap-3">
          <div
            class="bg-tertiary-fixed text-on-tertiary-fixed flex h-10 w-10 items-center justify-center rounded-full"
          >
            <span
              class="material-symbols-outlined"
              style="font-variation-settings: 'FILL' 1"
              >eco</span
            >
          </div>
          <span class="font-label-md text-label-md text-primary"
            >Eco-Impact</span
          >
        </div>
        <p
          class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed"
        >
          Join 10,000+ planners reducing household food waste daily.
        </p>
      </div>
    </aside>
  </div>
</template>
<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 1,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
</style>
