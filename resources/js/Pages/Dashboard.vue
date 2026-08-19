<script setup lang="ts">
import { ref, computed } from 'vue';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import MealCard from '../Components/MealCard.vue';

const dayOffset = ref(0);

const getFormattedDate = (offset: number) => {
  const date = new Date();
  date.setDate(date.getDate() + offset);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
const activeDateLabel = computed(() => {
  if (dayOffset.value === -1) return `Yesterday (${getFormattedDate(-1)})`;
  if (dayOffset.value === 1) return `Tomorrow (${getFormattedDate(1)})`;
  return `Today (${getFormattedDate(0)})`;
});

const prevDateLabel = computed(() => {
  if (dayOffset.value === 0) return 'Yesterday';
  if (dayOffset.value === 1) return 'Today';
  return '';
});

const nextDateLabel = computed(() => {
  if (dayOffset.value === 0) return 'Tomorrow';
  if (dayOffset.value === -1) return 'Today';
  return '';
});

const goPrevDay = () => {
  if (dayOffset.value > -1) dayOffset.value--;
};

const goNextDay = () => {
  if (dayOffset.value < 1) dayOffset.value++;
};

const leftChevronClasses = computed(() => {
  return dayOffset.value === -1
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary';
});

const rightChevronClasses = computed(() => {
  return dayOffset.value === 1
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary';
});

// Mock data array to drive the v-for loop
const todayMeals = ref([
  {
    id: 1,
    title: 'Protein Oatmeal & Berries',
    calories: 450,
    prepTime: 10,
    imageUrl:
      'https://lh3.googleusercontent.com/aida-public/AB6AXuDrPdwFVoid6FfI0L-M-qpbLkz8XtP2HJUNBhVmMqxGXc6iea-xztQaOlZWUIW6ZAHPmdE36hQXze0dD0Jf2yuxfrL5-rrAYgOA5wY4z0c1sTmSrH5K3FFQ1WfLWL0fk5Cc7vl6qNSIkbfSj-hL4K_A0SdnyJt9_KoVPtOl_Hgv89qq4iBhC8pP04S9BzuTD6bWyeFBdc7i9TkyFQfskuGMmxqa9Okfq2tZ_8ymnvTOEcmJ2hvyPjfP',
    imageAlt: 'Protein oatmeal with berries in a white bowl',
    isPrepared: false,
  },
  {
    id: 2,
    title: 'ZeroWaste Lemon Herb Chicken',
    calories: 750,
    prepTime: 25,
    imageUrl:
      'https://lh3.googleusercontent.com/aida-public/AB6AXuB7v_Qt5QaI_rtwDA4HcNZXyANBtfqk05xAHGhSEXFVcveJzeYJZ7CdeOuAZNl4-V0WJi71nPbpgaGox34GwOMv60qqI1RBQS1HskEu3w5wPnMfQWojU1ybtdgo2piVThXtJ7eXBvmPPUOo-tXWgGIheqI-oHEd9MyITE6w0EI3nxDmtvSY1E8VG_18ZfAZx00a3cdZ2kWpBfH2zvVGnqfQsfvd8Gtk5Ev6z7okEwKkRgGBuDOfjH1t',
    imageAlt: 'Lemon herb chicken breast plated on a white plate',
    isPrepared: false,
  },
  {
    id: 3,
    title: 'Leftover Veggie Stir-fry',
    calories: 550,
    prepTime: 15,
    imageUrl:
      'https://lh3.googleusercontent.com/aida-public/AB6AXuDu4P_047DwrMD2QKLAbFN7jaii9BTjhaa_B9hP8cCRuHUHy2z11v-5Q1F9OZKiXNUNYAsA3gLMfAs-CPPswEy3dGz-vL_wS72j3Q61iOn8ol5Rm1bCJNCV-Yr5qVJLP8HkDSi-qKdoLWsGffrmK-sUKMD0LftWYCPKZHkalh3oU_U96Zy7qtZ4qOPPLWACruM4xp6bsf31OgEd3t_7PhwSfgveAbn_kwBjSnwD_6xG-2RaTEm8Pgf4',
    imageAlt: 'Colorful vegetable stir-fry in a ceramic bowl',
    isPrepared: false,
  },
]);

const toggleMealStatus = (id: number) => {
  const meal = todayMeals.value.find((m) => m.id === id);
  if (meal) {
    meal.isPrepared = !meal.isPrepared;
  }
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="animate-fade-in space-y-8">
      <!-- Date Picker -->
      <div
        class="bg-surface-container-lowest mx-auto flex w-full max-w-md items-center justify-between rounded-xl p-4 shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
      >
        <button :class="leftChevronClasses" @click="goPrevDay">
          <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <div class="font-headline-md text-headline-md flex items-center gap-6">
          <span
            class="text-on-surface-variant font-body-lg text-body-lg hidden opacity-50 sm:inline"
            >{{ prevDateLabel }}</span
          >
          <span class="text-primary border-primary border-b-2 pb-1 font-bold">{{
            activeDateLabel
          }}</span>
          <span
            class="text-on-surface-variant font-body-lg text-body-lg hidden opacity-50 sm:inline"
            >{{ nextDateLabel }}</span
          >
        </div>
        <button :class="rightChevronClasses" @click="goNextDay">
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </div>

      <!-- Meal Grid (Grid is better than flex here) -->
      <div class="gap-gutter grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        <MealCard
          v-for="meal in todayMeals"
          :id="meal.id"
          :key="meal.id"
          :title="meal.title"
          :calories="meal.calories"
          :prep-time="meal.prepTime"
          :image-url="meal.imageUrl"
          :image-alt="meal.imageAlt"
          :is-prepared="meal.isPrepared"
          @toggle-cooked="toggleMealStatus(meal.id)"
        />
      </div>

      <!-- Weekly Analytics Placeholder -->
      <div
        class="bg-surface-container-lowest border-surface-container-high mt-12 rounded-xl border p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
      >
        <div class="mb-6 flex items-center justify-between">
          <h3 class="font-headline-lg text-headline-lg text-on-surface">
            Weekly Analytics
          </h3>
          <span class="material-symbols-outlined text-primary">monitoring</span>
        </div>
        <div
          class="bg-surface-container-low text-on-surface-variant border-outline-variant font-body-md text-body-md flex h-48 w-full items-center justify-center rounded-lg border border-dashed"
        >
          <div class="flex flex-col items-center gap-2">
            <span
              class="material-symbols-outlined text-tertiary-container text-4xl"
              >bar_chart</span
            >
            <span>Analytics visualization will appear here</span>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
