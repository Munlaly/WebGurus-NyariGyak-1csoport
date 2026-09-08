<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { registerSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

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
  <AuthLayout
    heading="Start your zero-waste journey"
    subheading="Create an account to set your dietary preferences and let us build a perfectly portioned, waste-free meal plan just for you."
    imageSrc="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
    imageAlt="Fresh ingredients photography"
  >
    <template #logo>
      <div
        class="font-headline-md text-primary mb-2 flex items-center gap-2 text-[24px] font-bold tracking-tight"
      >
        <span
          class="material-symbols-outlined"
          style="font-variation-settings: 'FILL' 1"
          >eco</span
        >
        Smart &amp; ZeroWaste
      </div>
    </template>

    <UForm
      :schema="registerSchema"
      :state="form"
      class="space-y-6"
      @submit="onSubmit"
    >
      <div class="space-y-4">
        <!-- Username -->
        <UFormField
          label="Full Name"
          name="username"
          :error="form.errors.username"
        >
          <UInput
            v-model="form.username"
            placeholder="Jane Doe"
            class="w-full"
            variant="outlined"
            :highlight="true"
          />
        </UFormField>

        <!-- Email -->
        <UFormField
          label="Email Address"
          name="email"
          :error="form.errors.email"
        >
          <UInput
            v-model="form.email"
            type="email"
            placeholder="jane@example.com"
            class="w-full"
            variant="outlined"
            :highlight="true"
          />
        </UFormField>

        <!-- Password -->
        <UFormField
          label="Password"
          name="password"
          :error="form.errors.password"
        >
          <UInput
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            class="w-full"
            variant="outlined"
            :highlight="true"
          />
        </UFormField>

        <!-- Confirm Password -->
        <UFormField
          label="Confirm Password"
          name="password_confirmation"
          :error="form.errors.password_confirmation"
        >
          <UInput
            v-model="form.password_confirmation"
            type="password"
            placeholder="••••••••"
            class="w-full"
            variant="outlined"
            :highlight="true"
          />
        </UFormField>
      </div>

      <!-- Submit Button -->
      <UButton
        type="submit"
        block
        :loading="form.processing"
        class="bg-primary text-on-primary font-headline-md hover:bg-primary/90 flex w-full items-center justify-center gap-2 rounded-full py-4 text-[18px] shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
      >
        <span class="material-symbols-outlined text-[20px]">lock</span>
        Create Account & Set Preferences
      </UButton>
    </UForm>

    <!-- Login Fallback -->
    <div class="text-on-surface-variant font-body-md mt-8 text-center">
      Already have an account?
      <Link
        class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
        :href="route('login')"
      >
        Log in
      </Link>
    </div>
  </AuthLayout>
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
