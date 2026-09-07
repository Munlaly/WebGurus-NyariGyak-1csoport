<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { loginSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

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
  <AuthLayout
    heading="Welcome back, Planner"
    subheading="Log in to manage your inventory and weekly meals."
    imageSrc="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
    imageAlt="Fresh vegetables and meal prep containers"
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
        />
      </UFormField>

      <div class="flex items-center justify-between">
        <label class="text-on-surface text-sm font-medium">Password</label>
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
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
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
