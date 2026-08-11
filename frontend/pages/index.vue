<template>
  <div class="bg-background text-on-background font-body-md min-h-screen flex flex-col antialiased selection:bg-primary-container selection:text-on-primary-container">
    
    <!-- PROGRESS BAR (Steps 1-7) -->
    <header v-if="currentStep >= 1 && currentStep <= 7" class="w-full pt-8 px-gutter flex flex-col items-center shrink-0 max-w-container-max mx-auto relative z-10 mt-4 md:mt-8">
      <div class="w-full max-w-3xl">
        <div class="flex justify-between items-center mb-2">
          <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Step {{ currentStep }} of 7</span>
          <span class="font-label-md text-label-md text-primary font-bold">{{ Math.round((currentStep / 7) * 100) }}%</span>
        </div>
        <div class="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden shadow-inner">
          <div class="h-full bg-primary rounded-full transition-all duration-500 ease-out" :style="{ width: `${(currentStep / 7) * 100}%` }"></div>
        </div>
      </div>
    </header>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col items-center justify-center w-full max-w-container-max mx-auto relative px-gutter py-12 mb-20">
      
      <!-- STEP 1: GOALS -->
      <div v-if="currentStep === 1" class="w-full max-w-3xl flex flex-col">
        <div class="text-left md:text-center mb-12">
          <h1 class="font-display text-display text-on-background mb-4">What are your primary goals?</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant">Select all that apply.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <button v-for="goal in availableGoals" :key="goal.value" @click="toggleGoal(goal.value)" type="button"
                  class="group relative flex items-center justify-between p-6 rounded-xl border-2 text-left cursor-pointer transition-all duration-200 ease-in-out shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
                  :class="[
                    quizData.goals.includes(goal.value) ? 'bg-primary-container/10 border-primary scale-[1.02] shadow-[0px_10px_30px_rgba(0,0,0,0.08)]' : 'bg-surface-container-lowest border-surface-variant hover:border-primary hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:scale-[1.02]', 
                    goal.fullWidth ? 'md:col-span-2' : ''
                  ]">
            <div class="flex items-center gap-4">
              <span class="material-symbols-outlined text-[32px] transition-colors" :class="quizData.goals.includes(goal.value) ? 'text-primary' : 'text-outline group-hover:text-primary'">{{ goal.icon }}</span>
              <span class="font-body-md text-body-md font-semibold transition-colors" :class="quizData.goals.includes(goal.value) ? 'text-primary' : 'text-on-surface group-hover:text-primary'">{{ goal.title }}</span>
            </div>
            <span v-if="quizData.goals.includes(goal.value)" class="material-symbols-outlined text-primary font-bold" style="font-variation-settings: 'FILL' 1;">check_circle</span>
          </button>
        </div>

        <!-- Login Bypass Button -->
        <div class="flex justify-center w-full mt-10 mb-6">
           <router-link to="/login" class="inline-flex items-center gap-2 px-6 py-2 bg-surface-container-low hover:bg-surface-container-high text-primary font-label-md text-label-md rounded-full shadow-sm transition-colors border border-surface-variant">
             <span class="material-symbols-outlined text-[18px]">login</span>
             Already have an account? Log in
           </router-link>
        </div>
      </div>

      <!-- STEP 2: DIETS -->
      <div v-else-if="currentStep === 2" class="w-full max-w-3xl flex flex-col">
        <header class="mb-10 text-center flex flex-col gap-4">
          <h1 class="font-display text-display text-on-surface">How do you prefer to eat?</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Select any specific diets or restrictions that apply to you.</p>
        </header>

        <div class="flex flex-wrap justify-center gap-4">
          
          <!-- Added exact calculated widths so it still looks like a 3-column grid -->
          <div v-for="diet in availableDiets" :key="diet.name" @click="toggleDiet(diet.name)"
               class="w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-11px)] relative rounded-xl p-4 border-2 shadow-sm transition-all duration-200 cursor-pointer flex flex-col items-center text-center hover:-translate-y-0.5"
               :class="quizData.meal_plan_preferences.includes(diet.name) ? 'bg-tertiary/10 border-primary-container' : 'bg-surface-container-lowest border-surface-variant hover:border-primary hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]'">
            
            <div v-if="quizData.meal_plan_preferences.includes(diet.name)" class="absolute top-2 right-2 bg-primary-container text-on-primary rounded-full w-6 h-6 flex items-center justify-center">
              <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            
            <span class="material-symbols-outlined text-4xl mb-2" :class="quizData.meal_plan_preferences.includes(diet.name) ? 'text-primary' : 'text-outline'" style="font-variation-settings: 'FILL' 0;">{{ diet.icon }}</span>
            <span class="font-headline-md text-body-md font-semibold text-on-surface">{{ diet.name }}</span>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">{{ diet.desc }}</p>
          </div>
        </div>
      </div>

      <!-- STEP 3: EXCLUSIONS -->
      <div v-else-if="currentStep === 3" class="w-full max-w-3xl flex flex-col">
        <header class="mb-10 text-center flex flex-col gap-4">
          <h1 class="font-display text-display text-on-surface">Any ingredients we should completely avoid?</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Search for specific foods or select common exclusions. We'll make sure they never show up in your meal plan.</p>
        </header>

        <!-- Search Bar -->
        <div class="relative w-full mb-8 group">
          <span class="material-symbols-outlined absolute left-4 top-1/2 transform -translate-y-1/2 text-on-surface-variant">search</span>
          <input type="text" v-model="currentCustomInput" @keydown.enter="addCustomIngredient" placeholder="Search ingredients (e.g., Mushrooms, Cilantro)..." class="w-full bg-surface-container-low rounded-xl pl-12 pr-4 py-4 font-body-lg text-body-lg border-2 border-transparent focus:outline-none focus:border-primary focus:bg-surface transition-colors duration-200 shadow-sm" />
        </div>

        <!-- Active Exclusions Tags -->
        <div class="mb-10 w-full" v-if="quizData.custom_dislikes.length > 0 || quizData.disliked_ingredients.length > 0">
          <div class="flex flex-wrap gap-3">
            <button v-for="(tag, index) in quizData.custom_dislikes" :key="'custom-'+index" @click="removeCustomIngredient(index)" class="flex items-center gap-2 bg-error-container/20 border border-error-container text-on-surface px-4 py-2 rounded-full font-label-md text-label-md cursor-pointer hover:scale-[1.02] transition-all">
              <span>{{ tag }}</span><span class="material-symbols-outlined text-[16px] text-error">close</span>
            </button>
            <button v-for="(tag, index) in quizData.disliked_ingredients" :key="'std-'+index" @click="toggleDislikedIngredient(tag)" class="flex items-center gap-2 bg-error-container/20 border border-error-container text-on-surface px-4 py-2 rounded-full font-label-md text-label-md cursor-pointer hover:scale-[1.02] transition-all">
              <span>{{ tag }}</span><span class="material-symbols-outlined text-[16px] text-error">close</span>
            </button>
          </div>
        </div>

        <!-- View 1: Big Category Cards & Quick Suggestions -->
        <div v-if="!selectedCategory" class="w-full">
          <h3 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-widest mb-4">Browse Categories</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <button v-for="category in dummyCategories" :key="category.id" @click="selectedCategory = category"
                    class="group flex flex-col items-center justify-center p-8 min-h-[160px] rounded-2xl bg-surface-container-lowest border-2 border-surface-variant hover:border-primary hover:bg-surface-container-low transition-all shadow-sm hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] cursor-pointer">
              <span class="material-symbols-outlined text-5xl text-primary mb-4 group-hover:scale-110 transition-transform">{{ category.icon }}</span>
              <span class="font-headline-md text-body-md font-bold text-center text-on-surface">{{ category.name }}</span>
            </button>
          </div>

          <h3 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-widest mb-4">Common Exclusions</h3>
          <div class="flex flex-wrap gap-3">
            <button v-for="suggestion in ['Shellfish', 'Spicy Foods', 'Soy', 'Tree Nuts']" :key="suggestion" @click="toggleDislikedIngredient(suggestion)" class="flex items-center gap-2 bg-surface-container-lowest border border-surface-variant px-4 py-2 rounded-full font-body-md text-body-md text-on-surface cursor-pointer shadow-sm hover:bg-surface-container-low hover:scale-[1.02] transition-all">
              <span class="material-symbols-outlined text-[18px] text-primary">{{ quizData.disliked_ingredients.includes(suggestion) ? 'check' : 'add' }}</span>
              <span>{{ suggestion }}</span>
            </button>
          </div>
        </div>

        <!-- View 2: Ingredients inside Selected Category -->
        <div v-else class="flex flex-col mb-8 w-full bg-surface-container-lowest p-6 md:p-8 rounded-2xl border border-surface-variant shadow-sm">
          <button @click="selectedCategory = null" class="self-start text-primary font-bold text-sm mb-6 flex items-center hover:underline">
            <span class="material-symbols-outlined text-sm mr-1">arrow_back</span> Back to Categories
          </button>
          
          <h3 class="text-2xl font-bold mb-6 font-headline-md text-on-surface flex items-center gap-3">
             <span class="material-symbols-outlined text-3xl text-primary">{{ selectedCategory.icon }}</span>
             {{ selectedCategory.name }}
          </h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-2">
            <label v-for="ingredient in selectedCategory.ingredients" :key="ingredient" 
                   class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all"
                   :class="quizData.disliked_ingredients.includes(ingredient) ? 'bg-primary-container/10 border-primary' : 'bg-surface border-surface-variant hover:border-primary hover:bg-surface-container-low'">
              <input type="checkbox" :value="ingredient" v-model="quizData.disliked_ingredients" class="mr-4 w-5 h-5 accent-primary text-primary focus:ring-primary border-outline rounded cursor-pointer">
              <span class="font-medium font-body-md" :class="quizData.disliked_ingredients.includes(ingredient) ? 'text-primary font-bold' : 'text-on-surface'">{{ ingredient }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- STEP 4: CALORIE GOAL -->
      <div v-else-if="currentStep === 4" class="w-full max-w-xl flex flex-col items-center">
        <div class="text-center mb-12 max-w-xl">
          <h1 class="font-display text-display text-on-surface mb-4">Set your daily calorie goal.</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant">We use this to perfectly portion your weekly menu and eliminate food waste.</p>
        </div>
        
        <div class="flex flex-col items-center justify-center gap-4 w-full max-w-md">
          
          <!-- Interactive Changer Container -->
          <div class="flex items-center justify-center gap-4 w-full">
            
            <button @click="decreaseCalories" class="shrink-0 w-16 h-16 rounded-full bg-surface-container-lowest border-2 border-surface-variant flex items-center justify-center hover:border-primary hover:text-primary transition-all shadow-sm active:scale-95 text-on-surface-variant">
              <span class="material-symbols-outlined text-3xl">remove</span>
            </button>
            
            <div class="relative w-full max-w-[200px] group">
              <!-- Replaced inline logic with handleCalorieBlur, and added @input to clear warning if they start typing -->
              <input aria-label="Daily Calorie Goal" type="number" min="1300" 
                     @blur="handleCalorieBlur"
                     @input="showCalorieWarning = false"
                     v-model="quizData.daily_calorie_target" 
                     class="w-full text-center text-5xl font-bold text-primary-container bg-surface-container-lowest border-2 border-surface-variant rounded-2xl py-6 px-2 shadow-sm focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 focus:outline-none transition-all duration-200 hover:shadow-md hover:border-outline-variant" />
            </div>
            
            <button @click="increaseCalories" class="shrink-0 w-16 h-16 rounded-full bg-surface-container-lowest border-2 border-surface-variant flex items-center justify-center hover:border-primary hover:text-primary transition-all shadow-sm active:scale-95 text-on-surface-variant">
              <span class="material-symbols-outlined text-3xl">add</span>
            </button>
          </div>
          
          <!-- Unit Label & Warning Message Container -->
          <div class="text-center flex flex-col items-center h-12">
            <span class="font-headline-md text-headline-md text-outline">kcal / day</span>
            <!-- The Warning Message (only visible when showCalorieWarning is true) -->
            <span v-if="showCalorieWarning" class="font-body-sm text-error font-semibold mt-1 transition-opacity animate-pulse">
              Calories cannot be set lower than 1300 or higher than 4000.
            </span>
          </div>

          <button @click="quizData.daily_calorie_target = 2000; showCalorieWarning = false" class="mt-4 group flex items-center justify-center gap-2 text-primary hover:text-primary-container transition-colors duration-200">
            <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform" style="font-variation-settings: 'FILL' 0;">help</span>
            <span class="font-body-sm text-body-sm underline-offset-4 group-hover:underline">Not sure? Reset to default (2000).</span>
          </button>
        </div>
      </div>

      <!-- STEP 5: BUDGET VS COMFORT -->
      <div v-else-if="currentStep === 5" class="w-full max-w-xl flex flex-col items-center">
        <header class="mb-10 text-center flex flex-col gap-4">
          <h1 class="font-display text-display text-on-surface">What is your shopping priority?</h1>
        </header>

        <div class="w-full flex flex-col gap-4">
          <label class="group relative flex items-center p-6 cursor-pointer bg-surface border-2 transition-all duration-200 rounded-xl hover:-translate-y-0.5"
                 :class="quizData.budget_or_comfort === 'Budget-friendly (Save money)' ? 'bg-primary-container/10 border-primary shadow-sm' : 'border-surface-container-highest hover:border-outline-variant'">
            <input type="radio" value="Budget-friendly (Save money)" v-model="quizData.budget_or_comfort" class="sr-only" />
            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center mr-4" :class="quizData.budget_or_comfort === 'Budget-friendly (Save money)' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant'">
              <span class="material-symbols-outlined text-2xl">savings</span>
            </div>
            <div class="flex-1">
              <h3 class="font-headline-md text-headline-md text-on-surface text-lg">Budget-friendly</h3>
              <p class="font-body-sm text-body-sm text-on-surface-variant">Minimize costs, prep items manually.</p>
            </div>
          </label>

          <label class="group relative flex items-center p-6 cursor-pointer bg-surface border-2 transition-all duration-200 rounded-xl hover:-translate-y-0.5"
                 :class="quizData.budget_or_comfort === 'Convenience & Comfort (Save time)' ? 'bg-primary-container/10 border-primary shadow-sm' : 'border-surface-container-highest hover:border-outline-variant'">
            <input type="radio" value="Convenience & Comfort (Save time)" v-model="quizData.budget_or_comfort" class="sr-only" />
            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center mr-4" :class="quizData.budget_or_comfort === 'Convenience & Comfort (Save time)' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant'">
              <span class="material-symbols-outlined text-2xl">fast_forward</span>
            </div>
            <div class="flex-1">
              <h3 class="font-headline-md text-headline-md text-on-surface text-lg">Convenience & Comfort</h3>
              <p class="font-body-sm text-body-sm text-on-surface-variant">Save time, buy pre-prepped ingredients.</p>
            </div>
          </label>
        </div>
      </div>

      <!-- STEP 6: HOUSEHOLD SIZE -->
      <div v-else-if="currentStep === 6" class="w-full max-w-xl flex flex-col items-center">
        <div class="text-center mb-10">
          <h1 class="font-headline-lg text-headline-lg text-on-surface mb-4">How many people are you cooking for?</h1>
          <p class="font-body-md text-body-md text-on-surface-variant">This helps us calculate precise ingredient quantities and minimize waste.</p>
        </div>

        <div class="w-full space-y-4">
          <label v-for="option in householdOptions" :key="option.value" 
                 class="group relative flex items-center p-6 cursor-pointer rounded-xl border-2 transition-all duration-200 hover:-translate-y-0.5"
                 :class="quizData.household_size === option.value ? 'bg-primary-container/10 border-primary shadow-sm scale-[1.02]' : 'bg-surface-container-lowest border-surface-variant hover:border-outline-variant'">
            <input type="radio" :value="option.value" v-model="quizData.household_size" class="sr-only" />
            <div class="flex items-center justify-center w-12 h-12 rounded-full mr-4 shrink-0 transition-colors" :class="quizData.household_size === option.value ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant group-hover:bg-surface-variant'">
              <span class="material-symbols-outlined text-2xl">{{ option.icon }}</span>
            </div>
            <div class="flex-1">
              <h3 class="font-headline-md text-headline-md text-on-surface text-lg">{{ option.title }}</h3>
              <p class="font-body-sm text-body-sm text-on-surface-variant">{{ option.desc }}</p>
            </div>
            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors" :class="quizData.household_size === option.value ? 'border-primary' : 'border-outline'">
              <div class="w-3 h-3 rounded-full transition-colors" :class="quizData.household_size === option.value ? 'bg-primary' : 'bg-transparent'"></div>
            </div>
          </label>
        </div>
      </div>

      <!-- STEP 7: PREP TIME -->
      <div v-else-if="currentStep === 7" class="w-full max-w-xl flex flex-col items-center">
        <div class="text-center mb-12">
          <h1 class="font-display text-display text-on-background mb-4">How much time do you usually have for meal prep?</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg mx-auto">We'll tailor your recipe recommendations to fit your daily schedule.</p>
        </div>

        <div class="w-full flex flex-col gap-4">
          <label v-for="time in prepTimeOptions" :key="time.value" 
                 class="group relative flex items-center gap-4 p-6 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]"
                 :class="quizData.prep_time_preference === time.value ? 'bg-primary-container/10 border-primary' : 'bg-surface border-surface-container-highest hover:border-outline-variant hover:bg-surface-container-low'">
            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center transition-colors" :class="quizData.prep_time_preference === time.value ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant group-hover:bg-surface-variant'">
              <span class="material-symbols-outlined text-2xl" :style="quizData.prep_time_preference === time.value ? 'font-variation-settings: \'FILL\' 1;' : ''">{{ time.icon }}</span>
            </div>
            <div class="flex-1 flex flex-col">
              <span class="font-headline-md text-headline-md text-on-background text-lg leading-tight mb-1">{{ time.title }}</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant">{{ time.desc }}</span>
            </div>
            <div class="flex-shrink-0 pl-4">
              <input type="radio" :value="time.value" v-model="quizData.prep_time_preference" class="w-5 h-5 text-primary border-outline focus:ring-primary focus:ring-offset-surface bg-surface cursor-pointer" />
            </div>
          </label>
        </div>
      </div>

      <!-- STEP 8: REGISTRATION SPLIT SCREEN -->
      <div v-else-if="currentStep === 8" class="fixed inset-0 w-full min-h-screen flex flex-col md:flex-row bg-background z-50">
        <section class="w-full md:w-1/2 lg:w-[500px] flex-shrink-0 bg-surface-container-lowest flex flex-col justify-center px-gutter py-[40px] md:px-[64px] shadow-[0px_4px_20px_rgba(0,0,0,0.04)] overflow-y-auto">
          <div class="max-w-md w-full mx-auto space-y-8">
            <header class="text-left space-y-4">
              <div class="font-headline-md text-headline-md font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">eco</span>
                Smart & ZeroWaste
              </div>
              <h1 class="font-display text-display text-on-surface">Your custom menu is ready.</h1>
              <p class="font-body-lg text-body-lg text-on-surface-variant">We've built your perfectly portioned <span class="text-primary font-bold">{{ quizData.daily_calorie_target }} kcal/day</span> waste-free plan. Create an account to save your preferences and unlock your week.</p>
            </header>

            <form @submit.prevent="submitRegistration" class="space-y-6">
              <div class="space-y-4">
                <div>
                  <label class="block font-label-md text-label-md text-on-surface-variant mb-2 uppercase tracking-wide">Full Name / Username</label>
                  <input type="text" v-model="form.username" required class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 font-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-colors duration-200" placeholder="Jane Doe" />
                </div>
                <div>
                  <label class="block font-label-md text-label-md text-on-surface-variant mb-2 uppercase tracking-wide">Email Address</label>
                  <input type="email" v-model="form.email" required class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 font-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-colors duration-200" placeholder="jane@example.com" />
                </div>
                
                <!-- Password with Toggle -->
                <div>
                  <label class="block font-label-md text-label-md text-on-surface-variant mb-2 uppercase tracking-wide">Password</label>
                  <div class="relative flex items-center">
                    <input :type="showPassword ? 'text' : 'password'" v-model="form.password" required class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 pr-12 font-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-colors duration-200" placeholder="••••••••" />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 text-on-surface-variant hover:text-primary focus:outline-none flex items-center justify-center transition-colors">
                      <span class="material-symbols-outlined text-xl">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                    </button>
                  </div>
                </div>
                
                <!-- Confirm Password with Toggle -->
                <div>
                  <label class="block font-label-md text-label-md text-on-surface-variant mb-2 uppercase tracking-wide">Confirm Password</label>
                  <div class="relative flex items-center">
                    <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" required class="w-full bg-surface-container-low border-none rounded-lg px-4 py-3 pr-12 font-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-colors duration-200" placeholder="••••••••" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-4 text-on-surface-variant hover:text-primary focus:outline-none flex items-center justify-center transition-colors">
                      <span class="material-symbols-outlined text-xl">{{ showConfirmPassword ? 'visibility_off' : 'visibility' }}</span>
                    </button>
                  </div>
                  <!-- Optional real-time matching warning -->
                  <span v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-xs text-error font-semibold mt-1 block">
                    Passwords do not match!
                  </span>
                </div>
              </div>
              <button type="submit" class="w-full bg-primary-container text-on-primary-container font-headline-md text-headline-md text-[18px] py-4 rounded-xl flex items-center justify-center gap-2 hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] transition-all duration-200">
                <span class="material-symbols-outlined">lock</span>
                Register & View Menu
              </button>
            </form>
            
            <div class="text-center pt-4">
              <p class="font-body-md text-body-md text-on-surface-variant">Already have an account? <a href="Login.vue" class="text-primary font-semibold hover:underline">Log in</a></p>
            </div>
            <button @click="currentStep--" class="w-full mt-2 text-on-surface-variant font-label-md hover:underline">Return to Quiz</button>
          </div>
        </section>

        <section class="hidden md:block flex-grow relative overflow-hidden bg-tertiary-container">
          <img alt="Fresh ingredients" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida/AP1WRLtwwvFMTc6LaK8czYngA7SthS1NkiOtDEGBiRZIwpte02w0EZtS698ph74AvqE2eIZMX_hsWls8V-KBuD7yqoxv54npmtpDv-KVtkHMIdnUc-Co4LTJaTzN64TJWbM2syNwEhuWe3KAPcIpOaUSSQZzK1MXxcMRh3GBM8ShPjrqjs19ueobRQOmQD0AV100F9SWiBBocYbYwj6X_1ANxetAcUi0BGKkZ9uhBj07dXiRqEMIKZwpZAyEZX0"/>
        </section>
      </div>

    </main>

    <!-- BOTTOM NAVIGATION (Only active during steps 1-7) -->
    <div v-if="currentStep >= 1 && currentStep <= 7" class="bg-surface shadow-sm border-t border-surface-container-highest shrink-0 w-full fixed bottom-0 left-0 z-50">
      <nav class="flex justify-between items-center px-gutter py-4 max-w-container-max mx-auto h-20">
        <button @click="prevStep" :disabled="currentStep === 1" class="flex items-center justify-center text-on-surface-variant px-6 py-3 font-label-md text-label-md rounded-xl transition-colors duration-200" :class="currentStep === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container-low hover:scale-[1.02] active:scale-95'">
          <span class="material-symbols-outlined mr-2">arrow_back</span> Back
        </button>
        <button
          @click="nextStep"
          :disabled="isNextDisabled"
          class="flex items-center justify-center rounded-xl px-8 py-3 font-label-md text-label-md font-bold transition-all duration-200"
          :class="isNextDisabled 
            ? 'bg-surface-container-high text-on-surface-variant opacity-50 cursor-not-allowed' 
            : 'bg-primary-container text-on-primary-container hover:scale-[1.02] hover:shadow-md active:scale-95'">
          {{ currentStep === 7 ? 'Complete Setup' : 'Next' }}
          <span v-if="currentStep < 7" class="material-symbols-outlined ml-2">arrow_forward</span>
        </button>
      </nav>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted, computed } from 'vue'

const currentStep = ref(1)

const showPassword = ref(false)
const showConfirmPassword = ref(false)

// UI Data Mappings based on HTML classes
const availableGoals = [
  { value: 'Lose weight', title: 'Lose weight', icon: 'monitor_weight' },
  { value: 'Gain weight (bulking)', title: 'Gain weight (bulking)', icon: 'fitness_center' },
  { value: 'Build muscle', title: 'Build muscle', icon: 'accessibility_new' },
  { value: 'Trying to eat healthy on a day to day basis', title: 'Trying to eat healthy on a day-to-day basis', icon: 'eco' },
  { value: 'Inspiration for my fridge', title: 'Inspiration for my fridge (ZeroWaste focus)', icon: 'kitchen', fullWidth: true }
]

const availableDiets = [
  { name: 'Omnivore', icon: 'restaurant', desc: 'I eat everything' },
  { name: 'Vegetarian', icon: 'eco', desc: 'No meat or poultry' },
  { name: 'Vegan', icon: 'cruelty_free', desc: 'No animal products' },
  { name: 'Gluten-Free', icon: 'agriculture', desc: 'Avoid wheat & gluten' },
  { name: 'Dairy-Free', icon: 'water_drop', desc: 'No milk or cheese' },
  { name: 'Keto / Low-Carb', icon: 'scale', desc: 'High fat, low carb' },
  { name: 'Nut-Free', icon: 'block', desc: 'Allergy safe' }
]

const dummyCategories = ref([
  { id: 1, name: 'Vegetables', icon: 'eco', ingredients: ['Mushrooms', 'Onions', 'Tomatoes', 'Broccoli', 'Bell Peppers'] },
  { id: 2, name: 'Dairy & Eggs', icon: 'water_drop', ingredients: ['Cheese', 'Milk', 'Eggs', 'Yogurt'] },
  { id: 3, name: 'Meat & Seafood', icon: 'set_meal', ingredients: ['Pork', 'Beef', 'Shrimp', 'Salmon', 'Chicken'] },
  { id: 4, name: 'Herbs & Spices', icon: 'nutrition', ingredients: ['Cilantro', 'Garlic', 'Oregano', 'Basil'] }
])
const selectedCategory = ref(null)

const householdOptions = [
  { value: 'Just for myself (1 person)', title: 'Just for myself', desc: '1 person', icon: 'person' },
  { value: 'Me and my partner (2 people)', title: 'Me and my partner', desc: '2 people', icon: 'group' },
  { value: 'For the entire family (3-5 people)', title: 'For the entire family', desc: '3-5+ people', icon: 'diversity_3' }
]

const prepTimeOptions = [
  { value: 'Lightning fast: under 20 minutes', title: 'Lightning fast', desc: 'Under 20 minutes', icon: 'bolt' },
  { value: 'Normal pace: 30-45 minutes', title: 'Normal pace', desc: '30-45 minutes', icon: 'schedule' },
  { value: 'Leisurely/weekend: over 1 hour', title: 'Leisurely / Weekend', desc: 'Over 1 hour', icon: 'restaurant_menu' }
]

// SSR Safe Setup
const quizData = reactive({
  goals: [],
  meal_plan_preferences: [],
  household_size: '',
  prep_time_preference: '',
  budget_or_comfort: '',
  daily_calorie_target: 2000,
  disliked_ingredients: [],
  custom_dislikes: []
})

onMounted(() => {
  const savedProgress = JSON.parse(sessionStorage.getItem('zeroWasteQuiz'))
  if (savedProgress) {
    Object.assign(quizData, savedProgress)
  }
  watch(quizData, (newState) => {
    sessionStorage.setItem('zeroWasteQuiz', JSON.stringify(newState))
  }, { deep: true })
})

const currentCustomInput = ref('')
const showCalorieWarning = ref(false)

function decreaseCalories() {
  if(quizData.daily_calorie_target <= 1300) {
    quizData.daily_calorie_target = 1300
    showCalorieWarning.value = true
  } else {
    quizData.daily_calorie_target -= 50
    showCalorieWarning.value = false
  }
}

function increaseCalories() {
  if(quizData.daily_calorie_target >= 4000) {
    quizData.daily_calorie_target = 4000;
    showCalorieWarning.value = true;
  } else {
    quizData.daily_calorie_target += 50
    showCalorieWarning.value = false
  }  
}

function handleCalorieBlur() {
  if(quizData.daily_calorie_target < 1300) {
    quizData.daily_calorie_target = 1300;
  } else if(quizData.daily_calorie_target > 4000) {
      quizData.daily_calorie_target = 4000;
  }
  showCalorieWarning.value = false
}

const isNextDisabled = computed(() => {
  if(currentStep.value === 4 && (quizData.daily_calorie_target < 1300 || quizData.daily_calorie_target > 4000)) {
    return true;
  }
  if(currentStep.value === 5 && !quizData.budget_or_comfort) {
    return true;
  }
  if(currentStep.value === 6 && !quizData.household_size) {
    return true;
  }
  if(currentStep.value === 7 && !quizData.prep_time_preference) {
    return true;
  }
})

// Form state
const form = reactive({
  username: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Methods
function nextStep() {
  currentStep.value++
}

function prevStep() {
  if (currentStep.value > 1) currentStep.value--
}

function toggleGoal(goalValue) {
  const index = quizData.goals.indexOf(goalValue)
  if (index === -1) {
    quizData.goals.push(goalValue)
  } else {
    quizData.goals.splice(index, 1)
  }
}

function toggleDiet(dietName) {
  const index = quizData.meal_plan_preferences.indexOf(dietName)
  if (index === -1) {
    quizData.meal_plan_preferences.push(dietName)
  } else {
    quizData.meal_plan_preferences.splice(index, 1)
  }
}

function toggleDislikedIngredient(ingredientName) {
  const index = quizData.disliked_ingredients.indexOf(ingredientName)
  if (index === -1) {
    quizData.disliked_ingredients.push(ingredientName)
  } else {
    quizData.disliked_ingredients.splice(index, 1)
  }
}

function addCustomIngredient(event) {
  event.preventDefault()
  const rawInput = currentCustomInput.value.trim()
  if (rawInput) {
    const newTags = rawInput.split(',').map(tag => tag.trim()).filter(tag => tag)
    newTags.forEach(tag => {
      if (!quizData.custom_dislikes.includes(tag) && !quizData.disliked_ingredients.includes(tag)) {
        quizData.custom_dislikes.push(tag)
      }
    })
    currentCustomInput.value = ''
  }
}

function removeCustomIngredient(index) {
  quizData.custom_dislikes.splice(index, 1)
}

async function submitRegistration() {
  if (form.password.trim() !== form.password_confirmation.trim()) {
    alert('Passwords do not match. Please check them before proceeding.')
    return
  }

  const authPayload = {
    username: form.username,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
  }

  try {
    const authResponse = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(authPayload)
    })

    const authData = await authResponse.json()

    if (!authResponse.ok) {
      alert('Error registering account. Please check your details.')
      return
    }

    const token = authData.token 
    const mappedBudgetSetting = quizData.budget_or_comfort === 'Budget-friendly (Save money)' ? 'budget_first' : 'comfort_first'
    
    const settingsPayload = {
      daily_calorie_target: quizData.daily_calorie_target,
      goals: quizData.goals,
      meal_plan_preferences: quizData.meal_plan_preferences,
      household_size: quizData.household_size,
      prep_time_preference: quizData.prep_time_preference,
      budget_or_comfort: mappedBudgetSetting,
      disliked_ingredients: quizData.disliked_ingredients,
      custom_dislikes: quizData.custom_dislikes 
    }

    const settingsResponse = await fetch('/api/user-settings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}` 
      },
      body: JSON.stringify(settingsPayload)
    })

    if (settingsResponse.ok) {
      alert('Account and preferences successfully saved!') 
      window.location.href = '/dashboard'
    } else {
      alert('Account created, but there was an error saving your preferences.')
    }
  } catch (error) {
    console.error('Submission failed:', error)
  }
}
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
/* Hide number input spinners for the Calorie step */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>