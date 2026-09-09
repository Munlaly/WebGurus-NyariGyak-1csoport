<script setup lang="ts">
import { reactive } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { loginSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const form = useForm({
  username: '',
  password: '',
});

const showPassword = reactive({
  password: false,
});

function toggleVisibility(field: 'password') {
  showPassword[field] = !showPassword[field];
}

function onSubmit() {
  form.post(route('login'), {
    onSuccess: () => form.reset('password'),
  });
}
</script>

<template>
  <AuthLayout
    heading="Welcome back, Planner"
    subheading="Log in to manage your inventory and weekly meals."
    image-src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
    image-alt="Fresh vegetables and meal prep containers"
  >
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
          size="xl"
        />
      </UFormField>

      <UFormField
        label="Password"
        name="password"
        :error="form.errors.password"
      >
        <UInput
          v-model="form.password"
          :type="showPassword.password ? 'text' : 'password'"
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
              @click="toggleVisibility('password')"
            >
              <span class="material-symbols-outlined text-[20px]">
                {{ showPassword.password ? 'visibility_off' : 'visibility' }}
              </span>
            </button>
          </template>
        </UInput>
      </UFormField>

      <div class="mt-2 flex justify-end">
        <Link
          v-if="route().has('password.request')"
          :href="route('password.request')"
          class="text-primary hover:text-primary-container text-sm font-medium transition-colors hover:underline"
        >
          Forgot password?
        </Link>
      </div>

      <UButton
        type="submit"
        block
        size="xl"
        color="primary"
        :loading="form.processing"
        class="w-full justify-center"
      >
        Log In
      </UButton>
    </UForm>

    <div class="text-on-surface-variant font-body-md mt-8 text-center">
      Don't have an account?
      <Link
        class="font-label-md text-label-md text-primary hover:text-primary-container ml-1 transition-colors hover:underline"
        :href="route('register')"
      >
        Sign up
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
