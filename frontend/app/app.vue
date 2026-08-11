<template>
  <div class="min-h-screen flex w-full bg-[#f8f9fa] text-[#191c1d] antialiased">
    
    <!-- STEP 0: WELCOME SCREEN -->
    <div v-if="currentStep === 0" class="w-full flex flex-col justify-center items-center p-8">
      <div class="max-w-md w-full text-center">
        <h2 class="text-2xl font-bold text-[#006e2f] mb-4">Smart & ZeroWaste</h2>
        <h1 class="text-3xl font-bold mb-4">Plan your meals, eliminate waste.</h1>
        <p class="text-[#3d4a3d] mb-8">Take a quick quiz to personalize your experience, or log in if you already have an account.</p>
        
        <div class="flex flex-col gap-4">
          <button @click="currentStep = 1" class="w-full bg-[#006e2f] text-white font-bold py-3 rounded-xl shadow-lg hover:opacity-90 transition">
            Start Quiz
          </button>
          <a href="/login" class="text-[#006e2f] font-bold hover:underline">
            Already have an account? Log in
          </a>
        </div>
      </div>
    </div>

    <!-- STEPS 1 to 5: GENERIC QUIZ QUESTIONS (Goals, Diets, Household, Prep Time, Budget) -->
    <div v-else-if="currentStep >= 1 && currentStep <= 5" class="w-full flex flex-col justify-center items-center p-8">
      <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm">
        <h2 class="text-xl font-bold text-[#006e2f] mb-2">Step {{ currentStep }} of 7</h2>
        
        <!-- Dynamic Question Title -->
        <h1 class="text-2xl font-bold mb-6">
          {{ getQuestionTitle(currentStep) }}
        </h1>

        <!-- Options container -->
        <div class="flex flex-col gap-3 mb-8">
          <label v-for="option in getQuestionOptions(currentStep)" :key="option" 
                 class="flex items-center p-3 rounded-xl border border-[#bccbb9] cursor-pointer hover:bg-[#edeeef] transition">
            <input :type="isMultiSelect(currentStep) ? 'checkbox' : 'radio'" 
                   :value="option" 
                   v-model="quizData[getCurrentKey(currentStep)]"
                   class="mr-3 accent-[#006e2f]">
            <span class="font-medium">{{ option }}</span>
          </label>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
          <button @click="prevStep" class="px-6 py-2 rounded-xl bg-[#edeeef] font-bold hover:bg-[#e1e3e4]">
            Previous
          </button>
          <button @click="nextStep" class="px-6 py-2 rounded-xl bg-[#006e2f] text-white font-bold hover:opacity-90">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- STEP 6: DAILY CALORIE TARGET -->
    <div v-else-if="currentStep === 6" class="w-full flex flex-col justify-center items-center p-8">
      <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm">
        <h2 class="text-xl font-bold text-[#006e2f] mb-2">Step 6 of 7</h2>
        
        <h1 class="text-2xl font-bold mb-2">What is your daily calorie target?</h1>
        <p class="text-[#3d4a3d] mb-6 text-sm">If you aren't sure, 2000 is a standard average.</p>

        <!-- Number input for calories -->
        <div class="flex flex-col gap-3 mb-8">
          <input type="number" 
                 v-model="quizData.daily_calorie_target" 
                 placeholder="e.g. 2000" 
                 class="w-full bg-[#f3f4f5] border-none rounded-xl px-4 py-4 font-bold text-lg outline-none focus:ring-2 focus:ring-[#006e2f]" />
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
          <button @click="prevStep" class="px-6 py-2 rounded-xl bg-[#edeeef] font-bold hover:bg-[#e1e3e4]">
            Previous
          </button>
          <button @click="nextStep" class="px-6 py-2 rounded-xl bg-[#006e2f] text-white font-bold hover:opacity-90">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- STEP 7: DISLIKED INGREDIENTS (Category Cards) -->
    <div v-else-if="currentStep === 7" class="w-full flex flex-col justify-center items-center p-8">
      <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm">
        <h2 class="text-xl font-bold text-[#006e2f] mb-2">Step 7 of 7</h2>
        <h1 class="text-2xl font-bold mb-6">Any ingredients that you are allergic to or dislike? (Optional)</h1>

        <!-- View 1: Category Cards Grid -->
        <div v-if="!selectedCategory" class="grid grid-cols-2 gap-4 mb-8">
          <button v-for="category in dummyCategories" :key="category.id" @click="selectedCategory = category"
                  class="flex flex-col items-center justify-center p-6 rounded-xl border border-[#bccbb9] hover:bg-[#edeeef] hover:border-[#006e2f] transition cursor-pointer">
            <span class="material-symbols-outlined text-4xl text-[#006e2f] mb-2">{{ category.icon }}</span>
            <span class="font-bold text-center">{{ category.name }}</span>
          </button>
        </div>

        <!-- View 2: Ingredients Checklist for the Selected Category -->
        <div v-else class="flex flex-col mb-8">
          <button @click="selectedCategory = null" class="self-start text-[#006e2f] font-bold text-sm mb-4 flex items-center hover:underline">
            <span class="material-symbols-outlined text-sm mr-1">arrow_back</span> Back to Categories
          </button>
          
          <h3 class="text-lg font-bold mb-4">{{ selectedCategory.name }}</h3>
          
          <div class="flex flex-col gap-3 max-h-[300px] overflow-y-auto pr-2">
            <label v-for="ingredient in selectedCategory.ingredients" :key="ingredient" 
                   class="flex items-center p-3 rounded-xl border border-[#bccbb9] cursor-pointer hover:bg-[#edeeef] transition">
              <input type="checkbox" :value="ingredient" v-model="quizData.disliked_ingredients" class="mr-3 accent-[#006e2f]">
              <span class="font-medium">{{ ingredient }}</span>
            </label>
          </div>
        </div>

        <!-- Custom "Other" Ingredient Input -->
        <div class="w-full mt-4 mb-8">
          <label class="font-bold mb-2 block">Other (Type an ingredient and press Enter)</label>
          
          <input type="text" 
                v-model="currentCustomInput" 
                @keydown.enter="addCustomIngredient"
                placeholder="e.g., Pineapple, Peanuts..." 
                class="w-full bg-[#f3f4f5] border-none rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-[#22c55e]" />

          <!-- Display the tags below the input -->
          <div class="flex flex-wrap gap-2 mt-3">
            <span v-for="(tag, index) in quizData.custom_dislikes" :key="tag" 
                  class="flex items-center px-3 py-1 bg-[#22c55e] text-white rounded-full text-sm font-semibold">
              {{ tag }}
              <button type="button" @click="removeCustomIngredient(index)" class="ml-2 hover:text-red-200">
                <span class="material-symbols-outlined text-sm align-middle">close</span>
              </button>
            </span>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
          <button @click="prevStep" class="px-6 py-2 rounded-xl bg-[#edeeef] font-bold hover:bg-[#e1e3e4]">
            Previous
          </button>
          <button @click="nextStep" class="px-6 py-2 rounded-xl bg-[#006e2f] text-white font-bold hover:opacity-90">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- STEP 8: FINAL REGISTRATION SCREEN -->
    <div v-else-if="currentStep === 8" class="min-h-screen flex w-full">
      <!-- Left Side: Form Area -->
      <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 lg:p-16 bg-white overflow-y-auto">
        <div class="max-w-md w-full">
          <div class="mb-8">
            <h2 class="text-[24px] font-bold text-[#006e2f] mb-6 tracking-tight">Smart &amp; ZeroWaste</h2>
            <h1 class="text-3xl font-bold mb-2">Create your account</h1>
            <p class="text-[#3d4a3d]">All your data have been saved, register into our app to don’t ever worry about what you eat!</p>
          </div>

          <form @submit.prevent="submitRegistration" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
              
              <!-- Full Name / Username -->
              <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-[#3d4a3d]">Full Name / Username</label>
                <input v-model="form.username" class="w-full bg-[#f3f4f5] border-none rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-[#22c55e]" placeholder="e.g. Jane Doe" type="text" required />
              </div>
              
              <!-- Email Address -->
              <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-[#3d4a3d]">Email address</label>
                <input v-model="form.email" class="w-full bg-[#f3f4f5] border-none rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-[#22c55e]" placeholder="you@example.com" type="email" required />
              </div>

              <!-- Password Field with Show/Hide Toggle -->
              <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-[#3d4a3d]">Password</label>
                <div class="relative flex items-center">
                  <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="w-full bg-[#f3f4f5] border-none rounded-lg px-4 py-3 pr-12 outline-none focus:ring-2 focus:ring-[#22c55e]" placeholder="••••••••" required />
                  <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span class="material-symbols-outlined text-sm">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                  </button>
                </div>
              </div>

              <!-- Confirm Password Field with Show/Hide Toggle -->
              <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-[#3d4a3d]">Confirm Password</label>
                <div class="relative flex items-center">
                  <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" class="w-full bg-[#f3f4f5] border-none rounded-lg px-4 py-3 pr-12 outline-none focus:ring-2 focus:ring-[#22c55e]" placeholder="••••••••" required />
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span class="material-symbols-outlined text-sm">{{ showConfirmPassword ? 'visibility_off' : 'visibility' }}</span>
                  </button>
                </div>
                <!-- Real-time red warning text -->
                <span v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-xs text-red-500 font-semibold mt-1">
                  Passwords do not match!
                </span>
              </div>

            </div>

            <!-- Action buttons -->
            <div class="flex gap-4 mt-4">
              <button type="button" @click="prevStep" class="w-1/3 bg-[#edeeef] text-[#191c1d] font-bold py-3 rounded-xl hover:bg-[#e1e3e4]">
                Previous
              </button>
              <button type="submit" class="w-2/3 bg-[#006e2f] text-white font-bold py-3 rounded-xl hover:shadow-lg transition">
                Create Account
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Side: Hero Image -->
      <div class="hidden lg:block w-1/2 relative bg-[#e1e3e4]">
        <img alt="Fresh ingredients photography" class="object-cover w-full h-full absolute inset-0" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBu4hOLWYtk1RXhFs1iaw2V7sa4ABaUDPoL5Pndc0OGEB8zms9z8Y6FL0MLwIFqXGpcsBj_vNmrQaGbSHmImkcbn6ZKzc_hnBo10TNoEKgmm38myvRJIaGGkxz1X4d2KMKhTzO6xKXmeF3jAYeLJFdwzrZA0k9Q4T0KrrUzNtnhLknevLs_4X_W7C1PS4EcopHXY4XpARpe1YlNjEwC7tVu7rmZY9LZ3zO0RBKZNp23fWp4kDa3qVLc"/>
      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'

const currentStep = ref(0) // 0 = Welcome, 1-5 = Generic, 6 = Calories, 7 = Ingredients, 8 = Registration

const showPassword = ref(false)
const showConfirmPassword = ref(false)

// State for the Category view in Step 7
const selectedCategory = ref(null)

// Dummy data for the categories (You will fetch this from Laravel later)
const dummyCategories = ref([
  { id: 1, name: 'Vegetables', icon: 'eco', ingredients: ['Mushrooms', 'Onions', 'Tomatoes', 'Broccoli', 'Bell Peppers'] },
  { id: 2, name: 'Dairy & Eggs', icon: 'water_drop', ingredients: ['Cheese', 'Milk', 'Eggs', 'Yogurt'] },
  { id: 3, name: 'Meat & Seafood', icon: 'set_meal', ingredients: ['Pork', 'Beef', 'Shrimp', 'Salmon', 'Chicken'] },
  { id: 4, name: 'Herbs & Spices', icon: 'nutrition', ingredients: ['Cilantro', 'Garlic', 'Spicy Peppers', 'Basil'] }
])

// Quiz reactive store
const savedProgress = JSON.parse(localStorage.getItem('zeroWasteQuiz')) || {}
const quizData = reactive({
  goals: savedProgress.goals || [],
  meal_plan_preferences: savedProgress.meal_plan_preferences || [],
  household_size: savedProgress.household_size || '',
  prep_time_preference: savedProgress.prep_time_preference || '',
  budget_or_comfort: savedProgress.budget_or_comfort || '', 
  daily_calorie_target: savedProgress.daily_calorie_target || 2000, 
  disliked_ingredients: savedProgress.disliked_ingredients || [],
  custom_dislikes: savedProgress.custom_dislikes || [] 
})

watch(quizData, (newState) => {
  localStorage.setItem('zeroWasteQuiz', JSON.stringify(newState))
}, { deep: true })

const currentCustomInput = ref()
const showCustomInput = ref(false)

// Registration credentials
const form = reactive({
  username: '',
  email: '',
  password: '',
  password_confirmation: '' 
})

function nextStep() {
  // Validate Steps 1 through 5 (Step 6 is a number input so it falls back on the default value)
  if (currentStep.value >= 1 && currentStep.value <= 5) {
    const currentKey = getCurrentKey(currentStep.value)
    const answer = quizData[currentKey]

    if (!isMultiSelect(currentStep.value) && !answer) {
      alert('Please select an option before continuing.')
      return 
    }
  }

  // Clear the selected category view if they leave Step 7
  if (currentStep.value === 7) {
    selectedCategory.value = null;
  }

  currentStep.value++
}

function prevStep() {
  if (currentStep.value > 0) currentStep.value--
  // Clear selected category if they navigate backwards from or within step 7
  if (currentStep.value === 7) selectedCategory.value = null;
}

// Configuration helper functions
function getQuestionTitle(step) {
  switch(step) {
    case 1: return 'What is your primary goal?'
    case 2: return 'What are your meal plan preferences?'
    case 3: return 'How many people do you usually cook for?'
    case 4: return 'How much time do you usually have for meal prep?'
    case 5: return 'What is your shopping priority?'
    default: return ''
  }
}

function getCurrentKey(step) {
  switch(step) {
    case 1: return 'goals'
    case 2: return 'meal_plan_preferences'
    case 3: return 'household_size'
    case 4: return 'prep_time_preference'
    case 5: return 'budget_or_comfort'
    default: return ''
  }
}

// Only Steps 1 and 2 use generic checkboxes. (Step 7 handles its own custom checkboxes).
function isMultiSelect(step) {
  return step === 1 || step === 2 
}

function getQuestionOptions(step) {
  switch(step) {
    case 1: return ['Lose weight', 'Gain weight (bulking)', 'Build muscle', 'Trying to eat healthy on a day to day basis', 'Inspiration for my fridge']
    case 2: return ['Omnivore', 'Vegetarian', 'Vegan', 'Gluten Free', 'Dairy Free', 'Keto/Low-Carb', 'Nut Free']
    case 3: return ['Just for myself (1 person)', 'Me and my partner (2 people)', 'For the entire family (3-5 people)']
    case 4: return ['Lightning fast: under 20 minutes', 'Normal pace: 30-45 minutes', 'Leisurely/weekend: over 1 hour']
    case 5: return ['Budget-friendly (Save money)', 'Convenience & Comfort (Save time)']
    default: return []
  }
}

function addCustomIngredient(event) {
  event.preventDefault(); 
  const rawInput = currentCustomInput.value.trim();
  if (rawInput) {
    const newTags = rawInput.split(',').map(tag => tag.trim()).filter(tag => tag);
    newTags.forEach(tag => {
      if (!quizData.custom_dislikes.includes(tag)) {
        quizData.custom_dislikes.push(tag);
      }
    });
    currentCustomInput.value = '';
  }
}

function removeCustomIngredient(index) {
  quizData.custom_dislikes.splice(index, 1);
}

async function submitRegistration() {
  if(form.password.trim() !== form.password_confirmation.trim()) {
    alert('Passwords do not match. Please check them before proceeding.');
    return;
  }

  // payload 1: 

  const payload = {
    username: form.username,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
  }
    
  try {
    const authResponse = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(authResponse)
    })

    const authData = await authResponse.json()

    if(!authResponse.ok) {
      alert('Error registering account. Please check your details.')
      return;
    }

    const token = authData.token();
    
    // payload 2
    const mappedBudgetSetting = quizData.budget_or_comfort === 'Budget-friendly (Save money)' ? 'budget_first' : 'comfort_first';
  
    const settingsPayload = {
      goals: quizData.goals,
      meal_plan_preferences: quizData.meal_plan_preferences,
      household_size: quizData.household_size,
      prep_time_preference: quizData.prep_time_preference,
      budget_or_comfort: mappedBudgetSetting,
      daily_calorie_target: quizData.daily_calorie_target,
      disliked_ingredients: quizData.disliked_ingredients,
      custom_dislikes: quizData.custom_dislikes
    }

    const settingResponse = await fetch('api/user-settings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(settingsPayload)
    })

    if(settingResponse.ok) {
      alert('Account and prefferences succesfully saved!')
      window.location.href = '/dashboard'
    } else {
      alert('Account created, but there was an error saving your preferences.')
    }
  } catch(error) {
    console.error('Submission failed: ', error)
  }
}
</script>