<script setup lang="ts">
import { z } from 'zod';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  email: string;
  token: string;
}>();

const schema = z
  .object({
    email: z.string().email('Invalid email address'),
    password: z.string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match!',
    path: ['password_confirmation'],
  });

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

function onSubmit() {
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <div
    class="bg-background text-on-surface font-body-md min-h-screen antialiased"
  >
    <div class="flex min-h-screen w-full">
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
              Set new password
            </h1>
            <p class="text-on-surface-variant font-body-md">
              Please enter your new password below.
            </p>
          </header>

          <UForm
            :schema="schema"
            :state="form"
            class="space-y-4"
            @submit="onSubmit"
          >
            <UFormField label="Email" name="email" :error="form.errors.email">
              <!-- Pre-filled from the URL/Controller props -->
              <UInput v-model="form.email" class="w-full" readonly />
            </UFormField>

            <UFormField
              label="New Password"
              name="password"
              :error="form.errors.password"
            >
              <UInput
                v-model="form.password"
                type="password"
                class="w-full"
                autofocus
              />
            </UFormField>

            <UFormField
              label="Confirm Password"
              name="password_confirmation"
              :error="form.errors.password_confirmation"
            >
              <UInput
                v-model="form.password_confirmation"
                type="password"
                class="w-full"
              />
            </UFormField>

            <UButton
              type="submit"
              block
              :loading="form.processing"
              class="mt-2 w-full justify-center"
            >
              Reset Password
            </UButton>
          </UForm>
        </div>
      </main>

      <aside class="bg-surface-variant relative hidden w-1/2 lg:block">
        <img
          alt="Fresh ingredients photography"
          class="absolute inset-0 h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
        />
        <div
          class="from-on-surface/5 absolute inset-0 bg-linear-to-tr to-transparent mix-blend-multiply"
        />
      </aside>
    </div>
  </div>
</template>
