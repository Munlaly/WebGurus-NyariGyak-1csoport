<template>
  <div class="flex min-h-screen w-full bg-background antialiased text-on-surface">
    <!-- Left Side: Form Area -->
    <main class="w-full md:w-1/2 bg-surface-container-lowest flex flex-col justify-center px-margin-mobile py-12 sm:px-12 md:px-margin-desktop lg:px-32 relative z-10">
      <div class="w-full max-w-105 mx-auto flex flex-col gap-8">
        
        <!-- Header Section -->
        <header class="flex flex-col gap-2">
          <h2 class="font-display text-4xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-[36px]" style="font-variation-settings: 'FILL' 1;">eco</span>
            LeKellFogyni
          </h2>
          <h1 class="font-headline-lg text-3xl font-semibold text-on-surface mt-4">Welcome back, Planner</h1>
          <p class="font-body-md text-base text-on-surface-variant mt-1">Log in to manage your inventory and weekly meals.</p>
        </header>

        <!-- Form Section -->
        <form class="flex flex-col gap-6" @submit.prevent="handleLogin">
          <!-- Email Field -->
          <div class="flex flex-col gap-2">
            <label class="font-label-md text-xs font-semibold tracking-wider uppercase text-on-surface-variant" for="email">Email address</label>
            <input 
              id="email" 
              v-model="form.email"
              type="email" 
              placeholder="hello@example.com" 
              required 
              class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 font-body-md text-on-surface placeholder:text-surface-dim focus:ring-2 focus:ring-primary-container focus:outline-none transition-shadow" 
            />
          </div>
          
          <!-- Password Field -->
          <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center">
              <label class="font-label-md text-xs font-semibold tracking-wider uppercase text-on-surface-variant" for="password">Password</label>
              <a href="#" class="font-label-md text-xs font-semibold text-primary hover:text-primary-container transition-colors">Forgot password?</a>
            </div>
            <div class="relative">
              <input 
                id="password" 
                v-model="form.password"
                type="password" 
                placeholder="••••••••" 
                required 
                class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 font-body-md text-on-surface placeholder:text-surface-dim focus:ring-2 focus:ring-primary-container focus:outline-none transition-shadow" 
              />
            </div>
          </div>
          
          <!-- Standard Submit Button -->
          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full mt-2 bg-primary-container text-on-primary font-label-md text-sm font-semibold py-4 rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-200 flex justify-center items-center gap-2"
            :class="isLoading ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:scale-[1.02] active:scale-95'"
          >
            <span v-if="isLoading" class="material-symbols-outlined text-[18px] animate-spin">refresh</span>
            <span v-else>{{ 'Log In' }}</span>
            <span v-if="!isLoading" class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </button>
          
          <!-- Divider -->
          <div class="relative flex items-center py-2">
            <div class="grow border-t border-surface-variant"></div>
            <span class="shrink-0 mx-4 text-on-surface-variant font-label-md text-xs font-semibold">OR</span>
            <div class="grow border-t border-surface-variant"></div>
          </div>
          
          <!-- Secondary Social Login Button -->
          <button 
            type="button" 
            @click="loginWithGoogle" 
            class="w-full bg-surface-container-lowest border-2 border-surface-variant text-on-surface font-label-md text-sm font-semibold py-3.5 rounded-xl hover:bg-surface-container-low hover:border-outline-variant hover:scale-[1.02] active:scale-95 transition-all duration-200 flex justify-center items-center gap-3"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Log in with Google
          </button>
        </form>

        <!-- Footer Section -->
        <div class="text-center mt-4">
          <p class="font-body-sm text-sm text-on-surface-variant">
            Don't have an account? 
            <!-- Router link assuming you use vue-router, fallback to standard link if not -->
            <router-link to="/" class="font-label-md text-xs font-semibold text-primary hover:text-primary-container transition-colors ml-1">Sign up</router-link>
          </p>
        </div>
      </div>
    </main>

    <!-- Right Side: Hero Image -->
    <aside class="hidden md:block md:w-1/2 relative bg-surface-container">
      <div class="absolute inset-0 w-full h-full">
        <img alt="Fresh vegetables and meal prep containers" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx4UiNtwUiNfR_h2KXIdhLca3Sr74uhM9fnM4ZOSu1fTG7VgWzTxWZBVkh6pEK8C0cecI4_YWswWEGO8pAsdqn8QvJ-xyjUNIoB3cMU59dKHNsr0Oc9-g47F3ZfbCqniI8vrBLBqsboiHL_GaR-j8vBzqWz80_6jAmEptVKgbdpO5a83yt-xMH1EEDz-ATh68On08xOqiv4i-7xgrdjEUvEKpA0itfFM5xlKg6ooBzzoa4XY6SY5l5">
        <div class="absolute inset-0 bg-linear-to-tr from-on-surface/5 to-transparent mix-blend-multiply"></div>
      </div>
      
      <div class="absolute bottom-12 right-12 bg-surface-container-lowest/90 backdrop-blur-md p-6 rounded-xl shadow-[0px_10px_30px_rgba(0,0,0,0.08)] max-w-60">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-full bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">eco</span>
          </div>
          <span class="font-label-md text-xs font-semibold text-primary uppercase tracking-wider">Eco-Impact</span>
        </div>
        <p class="font-body-sm text-sm text-on-surface-variant leading-relaxed">Join 10,000+ planners reducing household food waste daily.</p>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router' // Optional: if using vue-router

const router = useRouter() // Initialize router

const isLoading = ref(false)

const form = reactive({
  email: '',
  password: ''
})

const handleLogin = async () => {
  isLoading.value = true
  
  try {
    // TODO: Replace with your actual authentication API call (e.g., using Laravel Sanctum)
    // const response = await fetch('/api/login', { ... })
    
    // Simulating network request for UX purposes
    await new Promise(resolve => setTimeout(resolve, 800))
    
    console.log('Logging in with:', form.email)
    
    // Redirect to dashboard upon success
    // If using Vue Router:
    if (router) {
       router.push('/dashboard')
    } else {
       // Fallback to standard browser redirect
       window.location.href = '/dashboard'
    }
    
  } catch (error) {
    console.error('Login failed:', error)
    alert('Invalid email or password.')
  } finally {
    isLoading.value = false
  }
}

const loginWithGoogle = () => {
  // This usually hits a backend route that handles the OAuth redirect 
  // (e.g., Laravel Socialite endpoint)
  window.location.href = '/auth/google'
}
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
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