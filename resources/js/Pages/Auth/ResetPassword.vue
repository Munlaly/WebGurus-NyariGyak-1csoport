<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { resetPasswordSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import { reactive } from 'vue';

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

const showPassword = reactive({
  password: false,
  password_confirmation: false,
});

function toggleVisibility(field: 'password' | 'password_confirmation') {
  showPassword[field] = !showPassword[field];
}

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
    image-src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
    image-alt="Fresh ingredients photography"
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
          size="xl"
        />
      </UFormField>

      <UFormField
        label="New Password"
        name="password"
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          :type="showPassword.password ? 'text' : 'password'"
          class="w-full"
          autofocus
          variant="outlined"
          :highlight="true"
          size="xl"
        >
          <template #trailing>
            <button
              type="button"
              tabindex="-1"
              class="text-on-surface-variant hover:text-primary flex items-center transition-colors"
              @click="toggleVisibility('password')"
            >
              <span class="material-symbols-outlined text-[20px]">
                {{ showPassword.password ? 'visibility_off' : 'visibility' }}
              </span>
            </button>
          </template>
        </UInput>
      </UFormField>

      <UFormField
        label="Confirm Password"
        name="password_confirmation"
        :error="form.errors.password_confirmation"
      >
        <UInput
          v-model="form.password_confirmation"
          :type="showPassword.password_confirmation ? 'text' : 'password'"
          class="w-full"
          variant="outlined"
          :highlight="true"
          size="xl"
        >
          <template #trailing>
            <button
              type="button"
              tabindex="-1"
              class="text-on-surface-variant hover:text-primary flex items-center transition-colors"
              @click="toggleVisibility('password_confirmation')"
            >
              <span class="material-symbols-outlined text-[20px]">
                {{
                  showPassword.password_confirmation
                    ? 'visibility_off'
                    : 'visibility'
                }}
              </span>
            </button>
          </template>
        </UInput>
      </UFormField>

      <UButton
        type="submit"
        block
        size="xl"
        color="primary"
        :loading="form.processing"
        class="w-full justify-center"
      >
        Reset Password
      </UButton>
    </UForm>
  </AuthLayout>
</template>
