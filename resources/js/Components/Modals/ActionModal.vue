<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    show: boolean;
    title: string;
    processing?: boolean;
    submitText?: string;
    submitVariant?: 'primary' | 'error';
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
  }>(),
  {
    processing: false,
    submitVariant: 'primary',
    maxWidth: 'sm', // Defaults to sm so your existing smaller modals don't break
  },
);

const emit = defineEmits(['close', 'submit']);

// Map the prop to Tailwind max-width classes
const maxWidthClass = computed(() => {
  return {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
  }[props.maxWidth];
});
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div
      v-if="show"
      class="fixed inset-0 z-100 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
      @click.self="emit('close')"
    >
      <div
        :class="[
          'bg-surface border-outline-variant w-full rounded-2xl border p-6 shadow-2xl transition-all dark:bg-gray-900',
          maxWidthClass,
        ]"
      >
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-headline-sm text-headline-sm text-on-surface font-bold"
          >
            {{ title }}
          </h2>
          <button
            class="text-on-surface-variant hover:text-on-surface transition-colors"
            @click="emit('close')"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Dynamic Form Content via Slot -->
        <form class="flex flex-col gap-5" @submit.prevent="emit('submit')">
          <slot />

          <div class="mt-2 flex gap-3">
            <button
              type="button"
              class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex-1 rounded-xl py-3 font-bold transition-colors active:scale-95"
              @click="emit('close')"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              :class="[
                submitVariant === 'error'
                  ? 'bg-error text-on-error'
                  : 'bg-primary text-on-primary',
                'flex-1 rounded-xl py-3 font-bold shadow-sm transition-all hover:opacity-90 active:scale-95 disabled:opacity-50',
              ]"
            >
              {{ submitText || 'Confirm' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>
