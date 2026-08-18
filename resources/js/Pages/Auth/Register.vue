<script setup lang="ts">
import { z } from 'zod';
import { useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

// Define client side validation
const schema = z
  .object({
    username: z.string().min(2, 'Name must be at least 2 characters'),
    email: z.string().email('Invalid email address'),
    password: z.string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match!',
    path: ['password_confirmation'],
  });

const form = useForm({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
});

function onSubmit() {
  form.post(route('register'), {
    onSuccess: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <div
    class="bg-background text-on-surface font-body-md min-h-screen antialiased"
  >
    <div class="flex min-h-screen w-full">
      <!-- Left Side (Form Area) -->
      <div
        class="bg-surface-container-lowest flex w-full flex-col items-center justify-center overflow-y-auto p-8 lg:w-1/2 lg:p-16"
      >
        <div class="w-full max-w-md">
          <!-- Header -->
          <div class="mb-8">
            <h2
              class="font-display text-primary mb-6 text-[24px] font-bold tracking-tight"
            >
              Smart &amp; ZeroWaste
            </h2>
            <h1
              class="text-on-surface font-headline-lg mb-2 text-3xl font-bold"
            >
              Your custom menu is ready
            </h1>
            <p class="text-on-surface-variant font-body-md">
              We have built your perfectly portioned waste-free plan. Create an
              account to save your preferences ad unlock your week.
            </p>
          </div>

          <UForm
            :schema="schema"
            :state="form"
            class="space-y-4"
            @submit="onSubmit"
          >
            <UFormField
              label="Username"
              name="username"
              :error="form.errors.username"
            >
              <UInput v-model="form.username" />
            </UFormField>

            <UFormField label="Email" name="email" :error="form.errors.email">
              <UInput v-model="form.email" />
            </UFormField>

            <UFormField
              label="Password"
              name="password"
              :error="form.errors.password"
            >
              <UInput v-model="form.password" type="password" />
            </UFormField>

            <UFormField
              label="Confirm password"
              name="password_confirmation"
              :error="form.errors.password_confirmation"
            >
              <UInput v-model="form.password_confirmation" type="password" />
            </UFormField>

            <UButton type="submit" :loading="form.processing">
              Secure Account and View Menu</UButton
            >
          </UForm>

          <!-- Login Fallback -->
          <div class="text-on-surface-variant font-body-md mt-8 text-center">
            Already have an account?
            <Link
              class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
              :href="route('login')"
            >
              Sign in
            </Link>
          </div>
        </div>
      </div>

      <!-- Right Side (Hero Image) -->
      <div class="bg-surface-variant relative hidden w-1/2 lg:block">
        <img
          alt="Fresh ingredients photography"
          class="absolute inset-0 h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
        />
      </div>
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
