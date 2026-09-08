<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { resetPasswordSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const props = defineProps<{
  email: string;
  token: string;
}>();

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
  <AuthLayout
    heading="Set new password"
    subheading="Please enter your new password below."
    imageSrc="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
    imageAlt="Fresh ingredients photography"
  >
    <UForm
      :schema="resetPasswordSchema"
      :state="form"
      class="space-y-4"
      @submit="onSubmit"
    >
      <UFormField label="Email" name="email" :error="form.errors.email">
        <!-- Pre-filled from the URL/Controller props -->
        <UInput
          v-model="form.email"
          class="w-full"
          readonly
          variant="outlined"
          :highlight="true"
        />
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
          variant="outlined"
          :highlight="true"
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
        Reset Password
      </UButton>
    </UForm>
  </AuthLayout>
</template>
