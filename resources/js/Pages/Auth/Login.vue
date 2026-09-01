<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { loginSchema } from '../../Schemas/authSchema';

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
  <div
    class="bg-background text-on-surface font-body-md min-h-screen antialiased"
  >
    <div class="flex min-h-screen w-full flex-col md:flex-row">
      <!-- Left Side: Form Area (Centered on Mobile & Desktop) -->
      <main
        class="bg-surface-container-low z-10 flex w-full shrink-0 flex-col justify-center px-6 py-10 shadow-[0px_4px_20px_rgba(0,0,0,0.04)] md:w-1/2 md:px-16 lg:w-125"
      >
        <div class="mx-auto w-full max-w-md">
          <!-- Header Section -->
          <header class="mb-8">
            <h2
              class="font-display text-primary mb-2 text-[24px] font-bold tracking-tight"
            >
              Smart &amp; ZeroWaste
            </h2>
            <h1
              class="text-on-surface font-headline-lg mb-2 text-3xl font-bold"
            >
              Welcome back, Planner
            </h1>
            <p class="text-on-surface-variant font-body-md">
              Log in to manage your inventory and weekly meals.
            </p>
          </header>

          <UForm
            :schema="loginSchema"
            :state="form"
            class="space-y-4"
            @submit="onSubmit"
          >
            <UFormField
              label="Full Name"
              name="username"
              :error="form.errors.username"
            >
              <UInput
                v-model="form.username"
                class="w-full"
                variant="outlined"
                :highlight="true"
              />
            </UFormField>

            <div class="flex items-center justify-between">
              <label class="text-on-surface text-sm font-medium"
                >Password</label
              >
              <Link
                v-if="route().has('password.request')"
                :href="route('password.request')"
                class="text-primary hover:text-primary-container text-sm transition-colors hover:underline"
              >
                Forgot password?
              </Link>
            </div>
            <UFormField name="password" :error="form.errors.password">
              <UInput
                v-model="form.password"
                type="password"
                class="w-full"
                variant="outlined"
                :highlight="true"
              />
            </UFormField>

            <UButton
              type="submit"
              block
              :loading="form.processing"
              class="bg-primary text-on-primary font-headline-md hover:bg-primary/90 flex w-full items-center justify-center gap-2 rounded-full py-4 text-[18px] shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
            >
              Log In
              <span class="material-symbols-outlined text-[18px]"
                >arrow_forward</span
              >
            </UButton>
          </UForm>

          <!-- Footer Section -->
          <div class="text-on-surface-variant font-body-md mt-8 text-center">
            Don't have an account?
            <Link
              class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
              :href="route('register')"
            >
              Sign up
            </Link>
          </div>
        </div>
      </main>

      <!-- Right Side: Hero Image (Hidden on mobile/tablet, visible on lg+) -->
      <aside
        class="bg-surface-variant relative hidden grow overflow-hidden md:block"
      >
        <img
          alt="Fresh vegetables and meal prep containers"
          class="absolute inset-0 h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
        />
        <div
          class="from-on-surface/5 absolute inset-0 bg-linear-to-tr to-transparent mix-blend-multiply"
        />
      </aside>
    </div>
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
