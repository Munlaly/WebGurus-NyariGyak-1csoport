<template>
  <div
    class="bg-background text-on-surface flex min-h-screen w-full antialiased"
  >
    <!-- Left Side: Form Area -->
    <main
      class="bg-surface-container-lowest px-margin-mobile md:px-margin-desktop relative z-10 flex w-full flex-col justify-center py-12 sm:px-12 md:w-1/2 lg:px-32"
    >
      <div class="mx-auto flex w-full max-w-[420px] flex-col gap-8">
        <!-- Header Section -->
        <header class="flex flex-col gap-2">
          <h2
            class="font-display text-primary flex items-center gap-2 text-4xl font-bold"
          >
            <span
              class="material-symbols-outlined text-[36px]"
              style="font-variation-settings: 'FILL' 1"
              >eco</span
            >
            LeKellFogyni
          </h2>
          <h1
            class="font-headline-lg text-on-surface mt-4 text-3xl font-semibold"
          >
            Welcome back, Planner
          </h1>
          <p class="font-body-md text-on-surface-variant mt-1 text-base">
            Log in to manage your inventory and weekly meals.
          </p>
        </header>

        <!-- Form Section -->
        <form class="flex flex-col gap-6" @submit.prevent="handleLogin">
          <!-- Email Field -->
          <div class="flex flex-col gap-2">
            <label
              class="font-label-md text-on-surface-variant text-xs font-semibold tracking-wider uppercase"
              for="email"
              >Email address</label
            >
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="hello@example.com"
              required
              class="bg-surface-container-low font-body-md text-on-surface placeholder:text-surface-dim focus:ring-primary-container w-full rounded-lg border-none px-4 py-3 transition-shadow focus:ring-2 focus:outline-none"
            />
          </div>

          <!-- Password Field -->
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <label
                class="font-label-md text-on-surface-variant text-xs font-semibold tracking-wider uppercase"
                for="password"
                >Password</label
              >
              <a
                href="#"
                class="font-label-md text-primary hover:text-primary-container text-xs font-semibold transition-colors"
                >Forgot password?</a
              >
            </div>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                required
                class="bg-surface-container-low font-body-md text-on-surface placeholder:text-surface-dim focus:ring-primary-container w-full rounded-lg border-none px-4 py-3 transition-shadow focus:ring-2 focus:outline-none"
              />
            </div>
          </div>

          <!-- Standard Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="bg-primary-container text-on-primary font-label-md mt-2 flex w-full items-center justify-center gap-2 rounded-xl py-4 text-sm font-semibold shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-200"
            :class="
              isLoading
                ? 'cursor-not-allowed opacity-70'
                : 'hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] active:scale-95'
            "
          >
            <span
              v-if="isLoading"
              class="material-symbols-outlined animate-spin text-[18px]"
              >refresh</span
            >
            <span v-else>{{ 'Log In' }}</span>
            <span
              v-if="!isLoading"
              class="material-symbols-outlined text-[18px]"
              >arrow_forward</span
            >
          </button>

          <!-- Divider -->
          <div class="relative flex items-center py-2">
            <div class="border-surface-variant flex-grow border-t"></div>
            <span
              class="text-on-surface-variant font-label-md mx-4 flex-shrink-0 text-xs font-semibold"
              >OR</span
            >
            <div class="border-surface-variant flex-grow border-t"></div>
          </div>

          <!-- Secondary Social Login Button -->
          <button
            type="button"
            class="bg-surface-container-lowest border-surface-variant text-on-surface font-label-md hover:bg-surface-container-low hover:border-outline-variant flex w-full items-center justify-center gap-3 rounded-xl border-2 py-3.5 text-sm font-semibold transition-all duration-200 hover:scale-[1.02] active:scale-95"
            @click="loginWithGoogle"
          >
            <svg
              class="h-5 w-5"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                fill="#4285F4"
              />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853"
              />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                fill="#FBBC05"
              />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335"
              />
            </svg>
            Log in with Google
          </button>
        </form>

        <!-- Footer Section -->
        <div class="mt-4 text-center">
          <p class="font-body-sm text-on-surface-variant text-sm">
            Don't have an account?
            <!-- Router link assuming you use vue-router, fallback to standard link if not -->
            <router-link
              to="/"
              class="font-label-md text-primary hover:text-primary-container ml-1 text-xs font-semibold transition-colors"
              >Sign up</router-link
            >
          </p>
        </div>
      </div>
    </main>

    <!-- Right Side: Hero Image -->
    <aside class="bg-surface-container relative hidden md:block md:w-1/2">
      <div class="absolute inset-0 h-full w-full">
        <img
          alt="Fresh vegetables and meal prep containers"
          class="h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5"
        />
        <div
          class="from-on-surface/5 absolute inset-0 bg-gradient-to-tr to-transparent mix-blend-multiply"
        ></div>
      </div>

      <div
        class="bg-surface-container-lowest/90 absolute right-12 bottom-12 max-w-[240px] rounded-xl p-6 shadow-[0px_10px_30px_rgba(0,0,0,0.08)] backdrop-blur-md"
      >
        <div class="mb-2 flex items-center gap-3">
          <div
            class="bg-tertiary-fixed text-on-tertiary-fixed flex h-10 w-10 items-center justify-center rounded-full"
          >
            <span
              class="material-symbols-outlined"
              style="font-variation-settings: 'FILL' 1"
              >eco</span
            >
          </div>
          <span
            class="font-label-md text-primary text-xs font-semibold tracking-wider uppercase"
            >Eco-Impact</span
          >
        </div>
        <p class="font-body-sm text-on-surface-variant text-sm leading-relaxed">
          Join 10,000+ planners reducing household food waste daily.
        </p>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router'; // Optional: if using vue-router

const router = useRouter(); // Initialize router

const isLoading = ref(false);

const form = reactive({
  email: '',
  password: '',
});

const handleLogin = async () => {
  isLoading.value = true;

  try {
    // TODO: Replace with your actual authentication API call (e.g., using Laravel Sanctum)
    // const response = await fetch('/api/login', { ... })

    // Simulating network request for UX purposes
    await new Promise((resolve) => setTimeout(resolve, 800));

    console.log('Logging in with:', form.email);

    // Redirect to dashboard upon success
    // If using Vue Router:
    if (router) {
      router.push('/dashboard');
    } else {
      // Fallback to standard browser redirect
      window.location.href = '/dashboard';
    }
  } catch (error) {
    console.error('Login failed:', error);
    alert('Invalid email or password.');
  } finally {
    isLoading.value = false;
  }
};

const loginWithGoogle = () => {
  // This usually hits a backend route that handles the OAuth redirect
  // (e.g., Laravel Socialite endpoint)
  window.location.href = '/auth/google';
};
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}

/* Optional: Add a simple keyframe animation for the loading spinner */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
