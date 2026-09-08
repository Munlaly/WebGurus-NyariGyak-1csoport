<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { forgotPasswordSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

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
  <AuthLayout
    heading="Forgot your password?"
    subheading="No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one."
    image-src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
    image-alt="Fresh vegetables and meal prep"
  >
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
          <UInput
            v-model="form.email"
            class="w-full"
            autofocus
            variant="outlined"
            :highlight="true"
          />
        </UFormField>
      </div>

      <UButton
        type="submit"
        block
        :loading="form.processing"
        class="bg-primary text-on-primary font-headline-md hover:bg-primary/90 flex w-full items-center justify-center gap-2 rounded-full py-4 text-[18px] shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
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
  </AuthLayout>
</template>
