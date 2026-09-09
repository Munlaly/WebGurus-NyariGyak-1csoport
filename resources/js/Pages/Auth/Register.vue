<script setup lang="ts">
import { reactive } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { registerSchema } from '../../Schemas/authSchema';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const form = useForm({
  username: '',
  email: '',
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
  form.post(route('register'), {
    onSuccess: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <AuthLayout
    heading="Start your zero-waste journey"
    subheading="Create an account to set your dietary preferences and let us build a perfectly portioned, waste-free meal plan just for you."
    image-src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
    image-alt="Fresh ingredients photography"
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
            size="xl"
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
            size="xl"
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
            :type="showPassword.password ? 'text' : 'password'"
            placeholder="••••••••"
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

        <!-- Confirm Password -->
        <UFormField
          label="Confirm Password"
          name="password_confirmation"
          :error="form.errors.password_confirmation"
        >
          <UInput
            v-model="form.password_confirmation"
            :type="showPassword.password_confirmation ? 'text' : 'password'"
            placeholder="••••••••"
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
      </div>

      <!-- Submit Button -->
      <UButton
        type="submit"
        block
        size="xl"
        color="primary"
        :loading="form.processing"
        class="w-full justify-center"
      >
        Create Account
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
