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

          <UForm :state="formState" class="space-y-4" @submit="onSubmit">
            <UFormField label="Full name" name="name">
              <UInput v-model="formState.name" />
            </UFormField>

            <UFormField label="Email" name="email">
              <UInput v-model="formState.email" />
            </UFormField>

            <UFormField label="Password" name="password">
              <UInput v-model="formState.password" type="password" />
            </UFormField>

            <UFormField label="Confirm password" name="password_confirm">
              <UInput v-model="formState.password_confirm" />
            </UFormField>

            <UButton type="submit"> Secure Account and View Menu</UButton>
          </UForm>

          <!-- Login Fallback -->
          <div class="text-on-surface-variant font-body-md mt-8 text-center">
            Already have an account?
            <a
              class="text-primary font-bold transition-all hover:underline"
              href="#"
              >Log in</a
            >
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

<script setup lang="ts">
import { reactive } from 'vue';

const formState = reactive({
  name: '',
  email: '',
  password: '',
  password_confirm: '',
});

function onSubmit() {}
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 1,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
</style>
