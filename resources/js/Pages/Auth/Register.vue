<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { registerSchema } from '../../Schemas/authSchema';

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
    class="bg-background text-on-surface font-body-md flex min-h-screen antialiased"
  >
    <main class="flex min-h-screen w-full flex-col md:flex-row">
      <!-- Left Side (Form Area) -->
      <section
        class="bg-surface-container-low z-10 flex w-full shrink-0 flex-col justify-center px-6 py-10 shadow-[0px_4px_20px_rgba(0,0,0,0.04)] md:w-1/2 md:px-16 lg:w-125"
      >
        <div class="mx-auto w-full max-w-md space-y-8">
          <!-- Header -->
          <header class="space-y-4 text-left">
            <div
              class="font-headline-md text-primary flex items-center gap-2 text-[24px] font-bold tracking-tight"
            >
              <span
                class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' 1"
                >eco</span
              >
              Smart &amp; ZeroWaste
            </div>
            <h1
              class="text-on-surface font-display text-3xl font-bold tracking-tight"
            >
              Start your zero-waste journey
            </h1>
            <p class="text-on-surface-variant font-body-lg text-lg">
              Create an account to set your dietary preferences and let us build
              a perfectly portioned, waste-free meal plan just for you.
            </p>
          </header>

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
                  variant="none"
                  placeholder="Jane Doe"
                  class="bg-surface-container-lowest text-on-surface font-body-md focus:ring-primary w-full rounded-lg border border-black px-4 py-3 transition-colors duration-200 focus:ring-2"
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
                  variant="none"
                  placeholder="jane@example.com"
                  class="bg-surface-container-lowest text-on-surface font-body-md focus:ring-primary w-full rounded-lg border border-black px-4 py-3 transition-colors duration-200 focus:ring-2"
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
                  variant="none"
                  placeholder="••••••••"
                  class="bg-surface-container-lowest text-on-surface font-body-md focus:ring-primary w-full rounded-lg border border-black px-4 py-3 transition-colors duration-200 focus:ring-2"
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
                  variant="none"
                  placeholder="••••••••"
                  class="bg-surface-container-lowest text-on-surface font-body-md focus:ring-primary w-full rounded-lg border border-black px-4 py-3 transition-colors duration-200 focus:ring-2"
                />
              </UFormField>
            </div>

            <!-- Submit Button -->
            <UButton
              type="submit"
              :loading="form.processing"
              class="bg-primary-container text-on-primary-container font-headline-md flex w-full items-center justify-center gap-2 rounded-xl border border-black py-4 text-[18px] transition-all duration-200 hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]"
            >
              <span class="material-symbols-outlined text-[20px]">lock</span>
              Create Account & Set Preferences
            </UButton>
          </UForm>

          <!-- Login Fallback -->
          <div class="pt-4 text-center">
            <p class="text-on-surface-variant font-body-md text-base">
              Already have an account?
              <Link
                class="text-primary hover:text-primary-container ml-1 font-semibold transition-colors hover:underline"
                :href="route('login')"
              >
                Log in
              </Link>
            </p>
          </div>
        </div>
      </section>

      <!-- Right Side (Hero Image) -->
      <section
        class="bg-tertiary-container relative hidden grow overflow-hidden md:block"
      >
        <img
          alt="Fresh ingredients photography"
          class="absolute inset-0 h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"
        />
      </section>
    </main>
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
