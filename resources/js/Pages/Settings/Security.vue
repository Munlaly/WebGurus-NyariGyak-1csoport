<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import type { SecurityProps } from '../../Types/settingInterfaces';

const props = defineProps<SecurityProps>();

const activeTab = 'security';

// Basic Profile Info (Username & Email)
const profileForm = useForm({
  username: props.user.username,
  email: props.user.email,
});

//Password Management
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  profileForm.patch(route('settings.profile.update'), {
    preserveScroll: true,
  });
};

const updatePassword = () => {
  passwordForm.put(route('settings.password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <div
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
    >
      <!-- Section 1: Account Details -->
      <section class="py-8">
        <UForm
          :state="profileForm"
          class="grid grid-cols-1 gap-8 md:grid-cols-3"
          @submit.prevent="updateProfile"
        >
          <div class="md:col-span-1">
            <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
              Account Details
            </h2>
            <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
              Update your account's username and email address.
            </p>
          </div>

          <div class="space-y-6 md:col-span-2">
            <UFormField
              label="Username"
              name="username"
              :error="profileForm.errors.username"
            >
              <UInput
                v-model="profileForm.username"
                type="text"
                class="w-full max-w-md"
                autocomplete="username"
              />
            </UFormField>

            <UFormField
              label="Email Address"
              name="email"
              :error="profileForm.errors.email"
            >
              <UInput
                v-model="profileForm.email"
                type="email"
                class="w-full max-w-md"
                autocomplete="email"
              />
            </UFormField>

            <div class="flex items-center gap-4 pt-2">
              <UButton
                type="submit"
                color="primary"
                :loading="profileForm.processing"
                class="px-6 py-2 text-sm"
              >
                Save Details
              </UButton>

              <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0"
              >
                <p
                  v-if="profileForm.recentlySuccessful"
                  class="text-sm text-gray-600 dark:text-gray-400"
                >
                  Saved.
                </p>
              </Transition>
            </div>
          </div>
        </UForm>
      </section>

      <!-- Section 2: Update Password -->
      <section class="py-8">
        <UForm
          :state="passwordForm"
          class="grid grid-cols-1 gap-8 md:grid-cols-3"
          @submit.prevent="updatePassword"
        >
          <div class="md:col-span-1">
            <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
              Update Password
            </h2>
            <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
              Ensure your account is using a long, random password to stay
              secure.
            </p>
          </div>

          <div class="space-y-6 md:col-span-2">
            <UFormField
              label="Current Password"
              name="current_password"
              :error="passwordForm.errors.current_password"
            >
              <UInput
                v-model="passwordForm.current_password"
                type="password"
                class="w-full max-w-md"
                autocomplete="current-password"
              />
            </UFormField>

            <UFormField
              label="New Password"
              name="password"
              :error="passwordForm.errors.password"
            >
              <UInput
                v-model="passwordForm.password"
                type="password"
                class="w-full max-w-md"
                autocomplete="new-password"
              />
            </UFormField>

            <UFormField
              label="Confirm Password"
              name="password_confirmation"
              :error="passwordForm.errors.password_confirmation"
            >
              <UInput
                v-model="passwordForm.password_confirmation"
                type="password"
                class="w-full max-w-md"
                autocomplete="new-password"
              />
            </UFormField>

            <div class="flex items-center gap-4 pt-2">
              <UButton
                type="submit"
                color="primary"
                :loading="passwordForm.processing"
                class="px-6 py-2 text-sm"
              >
                Save Password
              </UButton>

              <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0"
              >
                <p
                  v-if="passwordForm.recentlySuccessful"
                  class="text-sm text-gray-600 dark:text-gray-400"
                >
                  Saved.
                </p>
              </Transition>
            </div>
          </div>
        </UForm>
      </section>
    </div>
  </SettingsLayout>
</template>
