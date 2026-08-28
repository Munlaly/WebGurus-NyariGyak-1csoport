<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { forgotPasswordSchema } from '../../Schemas/authSchema';

defineProps<{
  status?: string;
}>();

const form = useForm({
  email: '',
});

function onSubmit() {
  form.post(route('password.email'));
}
</script>

<template>
  <div
    class="bg-background text-on-surface font-body-md min-h-screen antialiased"
  >
    <div class="flex min-h-screen w-full">
      <!-- Left Side -->
      <main
        class="bg-surface-container-lowest flex w-full flex-col items-center justify-center overflow-y-auto p-6 sm:p-12 lg:w-1/2 lg:p-16"
      >
        <div class="w-full max-w-md">
          <header class="mb-8">
            <h2
              class="font-display text-primary mb-2 text-[24px] font-bold tracking-tight"
            >
              Smart &amp; ZeroWaste
            </h2>
            <h1
              class="text-on-surface font-headline-lg mb-2 text-3xl font-bold"
            >
              Forgot your password?
            </h1>
            <p class="text-on-surface-variant font-body-md">
              No problem. Just let us know your email address and we will email
              you a password reset link that will allow you to choose a new one.
            </p>
          </header>

          <!-- Success Message -->
          <div
            v-if="status"
            class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-600"
          >
            {{ status }}
          </div>

          <UForm
            :schema="forgotPasswordSchema"
            :state="form"
            class="space-y-4"
            @submit="onSubmit"
          >
            <div class="min-h-20">
              <UFormField label="Email" name="email" :error="form.errors.email">
                <UInput v-model="form.email" class="w-full" autofocus />
              </UFormField>
            </div>

            <UButton
              type="submit"
              block
              :loading="form.processing"
              class="mt-2 w-full justify-center"
            >
              Email Password Reset Link
            </UButton>
          </UForm>

          <div class="text-on-surface-variant font-body-md mt-8 text-center">
            Remember your password?
            <Link
              class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
              :href="route('login')"
            >
              Back to login
            </Link>
          </div>
        </div>
      </main>

      <!-- Right Side -->
      <aside class="bg-surface-variant relative hidden w-1/2 lg:block">
        <img
          alt="Fresh vegetables and meal prep"
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
