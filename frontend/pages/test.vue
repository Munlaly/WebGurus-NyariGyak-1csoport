<template>
  <div style="padding: 20px;">
    <button @click="goBack" style="background: #64748b; color: white; padding: 8px 16px; margin-bottom: 20px; cursor: pointer; border: none; border-radius: 5px; font-weight: bold; display: flex; align-items: center; gap: 5px;">
      ← Back
    </button>
    
    <!-- TEST: Cook Recipe Button -->
    <button @click="testCookMeal(1)" style="background: red; color: white; padding: 10px; margin-bottom: 20px; cursor: pointer; border: none; border-radius: 5px; margin-right: 10px;">
      TEST: Cook Recipe #1
    </button>

    <!-- The Test Button -->
    <button @click="testGenerateMealPlan" style="background: blue; color: white; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none;">
      TEST: Generate Weekly Meal Plan
    </button>
    <!-- Cooking Failed Modal Panel -->
    <div v-if="cookingFailed" class="modal-panel" style="border: 2px solid red; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
      <h3>You are missing some ingredients!</h3>
      <div class="split-view" style="display: flex; gap: 20px;">
        <div class="have-column">
          <h4>Ingredients I have:</h4>
          <ul>
            <li v-for="item in summary.have" :key="item.ingredient">
              ✅ {{ item.ingredient }} (Have {{ item.available }}, Need {{ item.required }})
            </li>
          </ul>
        </div>
        <div class="missing-column">
          <h4>Ingredients I don't have:</h4>
          <ul>
            <li v-for="item in summary.missing" :key="item.ingredient">
              ❌ {{ item.ingredient }} (Have {{ item.available }}, Need {{ item.required }})
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Generation Failed Panel -->
    <div v-if="generationFailed" class="modal-panel" style="margin-top: 20px; padding: 15px; border: 2px solid red; border-radius: 5px;">
      <h3 style="color: red;">{{ errorMessage }}</h3>
      <div v-if="errorSummary" style="margin-top: 10px;">
        <h4>Current valid meals found in DB:</h4>
        <ul>
          <li>🍳 Breakfasts: {{ errorSummary.breakfasts_found }}</li>
          <li>🥪 Lunches: {{ errorSummary.lunches_found }}</li>
          <li>🍲 Dinners: {{ errorSummary.dinners_found }}</li>
        </ul>
      </div>
    </div>

    <!-- The Success Panel that shows the schedule -->
    <div v-if="weeklyPlan" style="margin-top: 30px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
          <h2>Your Generated Meal Plan</h2>
          <p><strong>Daily Target:</strong> {{ targetCalories }} kcal</p>
        </div>
        <!-- SAVE PLAN TEST BUTTON -->
        <button @click="saveWeeklyPlan" style="background: #10b981; color: white; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none;">
          💾 Save Plan to DB
        </button>
      </div>
      <div style="display: grid; gap: 20px;">
        <div v-for="(dayData, dayName) in weeklyPlan" :key="dayName" style="border: 1px solid #ccc; padding: 15px; border-radius: 8px;">
          
          <h3 style="margin-bottom: 5px;">{{ dayName }} ({{ dayData.total_calories }} kcal)</h3>
          
          <span v-if="dayData.perfect_match" style="color: green; font-weight: bold;">✅ Perfect Calorie Match!</span>
          <span v-else style="color: orange; font-weight: bold;">⚠️ Closest Match Found (Maxed out attempts)</span>
          
          <ul style="margin-top: 15px; list-style: none; padding: 0;">
            <li v-for="(meal, index) in dayData.meals" :key="meal.id || index" style="display: flex; align-items: center; gap: 15px; border-bottom: 1px solid #eee; padding: 10px 0;">
              
              <!-- Meal Thumbnail Image (Supports Local Storage & External URLs) -->
              <img :src="getImageUrl(meal.image)" :alt="meal.title || meal.name" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">

              <div style="flex: 1;">
                <strong>{{ meal.title || meal.name }}</strong> 
                <br>
                <span style="font-size: 0.9em; color: #666;">
                  Calories: {{ meal.calories }} | Prep Time: {{ meal.prep_time_minutes }} mins
                </span>
                <span v-if="index === 3" style="font-size: 0.8em; font-weight: bold; color: #f59e0b; background: #fef3c7; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">SNACK</span>
              </div>
              
              <!-- Action Buttons -->
              <div style="display: flex; gap: 8px;">
                <!-- Single Meal Regenerate Button -->
                <button @click="regenerateSingleMeal(dayName, index)" title="Regenerate this meal" style="background: #e2e8f0; border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                  <span class="material-symbols-outlined" style="font-size: 18px;">🔄</span>
                </button>

                <!-- Delete Snack Button (Only shows for the 4th item, index 3) -->
                <button v-if="index === 3" @click="deleteSnack(dayName)" title="Remove snack" style="background: #fee2e2; border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                  <span class="material-symbols-outlined" style="font-size: 18px;">🗑️</span>
                </button>
              </div>
            </li>
          </ul>

          <!-- Add Snack Button (Only shows if no snack exists) -->
          <button v-if="!dayData.has_snack" @click="addSnack(dayName)" style="margin-top: 10px; width: 100%; padding: 8px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 5px; cursor: pointer; font-weight: bold; color: #64748b;">
            + Add a Snack
          </button>

        </div>
      </div>
    </div>

    <hr style="margin: 40px 0;">

    <!-- NEW TEST: Create Custom Recipe -->
    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1;">
      <h2>TEST: Create Custom Recipe</h2>
      <p style="margin-bottom: 15px; color: #64748b;">This uses FormData to send text, nested arrays (ingredients), and an image file simultaneously.</p>
      
      <div style="display: grid; gap: 15px; max-width: 600px;">
        <input v-model="newRecipe.name" type="text" placeholder="Recipe Name" style="padding: 8px;">
        
        <textarea v-model="newRecipe.instructions" placeholder="1. Step one&#10;2. Step two" rows="4" style="padding: 8px;"></textarea>
        
        <input v-model="newRecipe.prep_time_minutes" type="number" placeholder="Prep Time (mins)" style="padding: 8px;">
        
        <!-- Macros Group -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <input v-model="newRecipe.calories" type="number" placeholder="Calories" style="padding: 8px; flex: 1;">
          <input v-model="newRecipe.protein" type="number" placeholder="Protein (g)" style="padding: 8px; flex: 1;">
          <input v-model="newRecipe.fat" type="number" placeholder="Fat (g)" style="padding: 8px; flex: 1;">
          <input v-model="newRecipe.carbs" type="number" placeholder="Carbs (g)" style="padding: 8px; flex: 1;">
        </div>

        <!-- Meal Types Select Box -->
        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Meal Types (Hold Ctrl/Cmd to select multiple):</label>
          <select multiple v-model="newRecipe.meal_types" style="padding: 8px; width: 100%; height: 80px; background: white; border: 1px solid #ccc; border-radius: 4px;">
            <option value="breakfast">Breakfast</option>
            <option value="lunch">Lunch</option>
            <option value="dinner">Dinner</option>
          </select>
        </div>
        
        <!-- Image Upload Input -->
        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Upload Image:</label>
          <input type="file" @change="handleFileUpload" accept="image/png, image/jpeg" style="padding: 8px; background: white; border: 1px solid #ccc; width: 100%;">
        </div>

        <!-- Public Toggle Checkbox -->
        <div style="display: flex; align-items: center; gap: 10px; padding: 5px 0;">
          <input type="checkbox" id="publicToggle" v-model="newRecipe.is_public" :true-value="1" :false-value="0" style="width: 20px; height: 20px; cursor: pointer;">
          <label for="publicToggle" style="font-weight: bold; cursor: pointer;">
            Make Public <span style="font-weight: normal; color: #64748b; font-size: 0.9em;">(Other users can see this recipe)</span>
          </label>
        </div>

        <button @click="testCreateRecipe" style="background: #8b5cf6; color: white; padding: 12px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none; margin-top: 10px;">
          🚀 Submit Custom Recipe
        </button>
      </div>
    </div>

    <hr style="margin: 40px 0;">

    <!-- DIRECT LOCAL STORAGE VERIFICATION SECTION -->
    <div style="background: #ffffff; padding: 20px; border-radius: 8px; border: 2px solid #3b82f6; margin-top: 30px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
          <h2>Verify Local Storage Image</h2>
          <p style="color: #64748b; margin: 0;">Click below to fetch your created recipes directly and check "Grandmas Test Lasagna".</p>
        </div>
        <button @click="fetchUserRecipes" style="background: #2563eb; color: white; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none;">
          🔍 Load My Custom Recipes
        </button>
      </div>

      <div v-if="userRecipes.length > 0" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 15px;">
        <div v-for="recipe in userRecipes" :key="recipe.id" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #f8fafc; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
          <!-- Renders the local storage image path via getImageUrl -->
          <img :src="getImageUrl(recipe.image)" :alt="recipe.name" style="width: 100%; height: 160px; object-fit: cover;">
          <div style="padding: 15px;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b;">{{ recipe.name }}</h3>
            <p style="margin: 0 0 5px 0; font-size: 0.85em; color: #475569;">
              <strong>DB Image Path:</strong> <code style="background: #e2e8f0; padding: 2px 4px; border-radius: 4px;">{{ recipe.image || 'null' }}</code>
            </p>
            <p style="margin: 0; font-size: 0.85em; color: #059669;">
              <strong>Calories:</strong> {{ recipe.calories }} kcal | <strong>Prep:</strong> {{ recipe.prep_time_minutes }}m
            </p>
          </div>
        </div>
      </div>
      <p v-else style="color: #64748b; font-style: italic; margin-top: 10px;">Click the blue button above to load your saved recipes and verify the local file display.</p>
    </div>

    <hr style="margin: 40px 0;">

    <!-- DIRECT SETTINGS VERIFICATION SECTION -->
    <div style="background: #ffffff; padding: 20px; border-radius: 8px; border: 2px solid #10b981; margin-top: 30px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
          <h2>Settings API Interface</h2>
          <p style="color: #64748b; margin: 0;">Fetch, edit, and save your core preferences.</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <button @click="testFetchSettings" style="background: #059669; color: white; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none;">
            ⬇️ Fetch Settings
          </button>
          <button @click="testUpdateSettings" style="background: #f59e0b; color: white; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; border: none;">
            ⬆️ Save Changes
          </button>
        </div>
      </div>
      
      <!-- Interactive Form (Only shows after data is fetched) -->
      <div v-if="fetchedSettings" style="background: #f8fafc; padding: 20px; border-radius: 5px; border: 1px solid #e2e8f0; margin-top: 15px; display: grid; gap: 15px; max-width: 600px;">
        
        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Daily Calorie Target (1300 - 4000):</label>
          <input type="number" v-model="settingsForm.daily_calorie_target" min="1300" max="4000" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Prep Time Preference:</label>
          <select v-model="settingsForm.prep_time_preference" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
            <option :value="20">Lightning fast (< 20 mins)</option>
            <option :value="45">Normal pace (30-45 mins)</option>
            <option :value="999">Leisurely (1h+)</option>
          </select>
        </div>

        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Goals (Hold Ctrl/Cmd to select multiple):</label>
          <select multiple v-model="settingsForm.goals" style="padding: 8px; width: 100%; height: 100px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="lose_weight">Lose weight</option>
            <option value="gain_weight">Gain weight</option>
            <option value="build_muscle">Build muscle</option>
            <option value="eat_healthy">Eat healthy</option>
            <option value="zero_waste">Zero Waste</option>
          </select>
        </div>

        <div>
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">Diet Type (Hold Ctrl/Cmd to select multiple):</label>
          <select multiple v-model="settingsForm.meal_plan_preference" style="padding: 8px; width: 100%; height: 120px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="omnivore">Omnivore</option>
            <option value="vegetarian">Vegetarian</option>
            <option value="vegan">Vegan</option>
            <option value="keto">Keto</option>
            <option value="gluten_free">Gluten-Free</option>
            <option value="dairy_free">Dairy-Free</option>
            <option value="nut_free">Nut-Free</option>
          </select>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  router.back()
}

// ---------------------------------------------------------
// LOCAL STORAGE VERIFICATION STATE
// ---------------------------------------------------------
const userRecipes = ref([])

const fetchUserRecipes = async () => {
  try {
    const response = await $fetch(`https://projekt.ddev.site:33001/api/user/recipes`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
        'Accept': 'application/json'
      }
    })
    // Handles both direct array returns or standard resource collection wrappers
    userRecipes.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Failed to load user recipes:', error.data || error)
    alert('Failed to load user recipes. Check console.')
  }
}

// ---------------------------------------------------------
// EXISTING MEAL PLAN & COOKING LOGIC
// ---------------------------------------------------------
const generationFailed = ref(false)
const errorMessage = ref('')
const errorSummary = ref(null)
const weeklyPlan = ref(null)
const targetCalories = ref(0)
const cookingFailed = ref(false)
const summary = ref({ have: [], missing: [] })
const currentDayMeals = ref([])

const testGenerateMealPlan = async () => {
    generationFailed.value = false
    weeklyPlan.value = null
    errorMessage.value = ''
    errorSummary.value = null

    try {
        const response = await $fetch(`https://projekt.ddev.site:33001/api/meal-plan/generate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
                'Accept': 'application/json'
            }
        })
        
        weeklyPlan.value = response.plan
        targetCalories.value = response.target_calories
        
    } catch (error) {
        console.error('Failed Payload:', error.data || error)
        generationFailed.value = true
        if (error.data && error.data.message) {
            errorMessage.value = error.data.message
            errorSummary.value = error.data.summary || null
        } else {
            errorMessage.value = 'An unexpected error occurred. Check the console.'
        }
    }
}

const saveWeeklyPlan = async () => {
    try {
        const response = await $fetch(`https://projekt.ddev.site:33001/api/meal-plan/save`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: {
                plan: weeklyPlan.value
            }
        });

        alert(response.message);
    } catch (error) {
        console.error('Failed to save plan:', error.data || error);
        alert('Failed to save meal plan. Check console.');
    }
}

const testCookMeal = async (recipeId) => {
  cookingFailed.value = false;
    try {
        const response = await $fetch(`https://projekt.ddev.site:33001/api/recipes/${recipeId}/cook`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
                'Accept': 'application/json'
            }
        });
        
        alert(response.message);
        
    } catch (error) {
        if(error.data && error.data.summary) {
          cookingFailed.value = true;
          summary.value = error.data.summary;
        } else {
          console.error('Failed:', error.data || error);
          alert('An unexpected error occured. Check the console');
        }
    }
}

async function regenerateSingleMeal(dayName, mealIndex) {
  // Added index 3 for snacks
  const typeMap = { 0: 'breakfast', 1: 'lunch', 2: 'dinner', 3: 'snack' }
  const mealType = typeMap[mealIndex]

  try {
    const response = await fetch('https://projekt.ddev.site:33001/api/meal-plan/regenerate-meal', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772'
      },
      body: JSON.stringify({ meal_type: mealType })
    })

    const data = await response.json()

    if (data.success) {
      if (weeklyPlan.value?.[dayName]?.meals) {
        weeklyPlan.value[dayName].meals[mealIndex] = data.recipe

        const newTotal = weeklyPlan.value[dayName].meals.reduce(
          (sum, m) => sum + m.calories, 0
        )
        weeklyPlan.value[dayName].total_calories = newTotal

        const target = targetCalories.value;
        const minRange = target * 0.85;
        const maxRange = target * 1.15;

        weeklyPlan.value[dayName].perfect_match = (newTotal >= minRange && newTotal <= maxRange);
      }
    } else {
      alert(data.message || 'Could not regenerate meal.')
    }
  } catch (error) {
    console.error('Failed to regenerate meal:', error)
  }
}

const deleteSnack = (dayName) => {
  if (weeklyPlan.value[dayName].meals.length === 4) {
    // Remove the snack (the 4th item in the array)
    const removedSnack = weeklyPlan.value[dayName].meals.pop()
    
    // Update calories and UI state
    weeklyPlan.value[dayName].total_calories -= removedSnack.calories
    weeklyPlan.value[dayName].has_snack = false
    
    // Recalculate if it still hits the perfect match range
    const target = targetCalories.value;
    const newTotal = weeklyPlan.value[dayName].total_calories;
    weeklyPlan.value[dayName].perfect_match = (newTotal >= target * 0.85 && newTotal <= target * 1.15);
  }
}

const addSnack = async (dayName) => {
  try {
    // Fetch a single snack using your existing regeneration endpoint
    const response = await fetch('https://projekt.ddev.site:33001/api/meal-plan/regenerate-meal', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772'
      },
      body: JSON.stringify({ meal_type: 'snack' })
    })

    const data = await response.json()

    if (data.success) {
      // Append the new snack to the day's meals
      weeklyPlan.value[dayName].meals.push(data.recipe)
      
      // Update calories and UI state
      weeklyPlan.value[dayName].total_calories += data.recipe.calories
      weeklyPlan.value[dayName].has_snack = true
      
      // Recalculate if it hits the perfect match range
      const target = targetCalories.value;
      const newTotal = weeklyPlan.value[dayName].total_calories;
      weeklyPlan.value[dayName].perfect_match = (newTotal >= target * 0.85 && newTotal <= target * 1.15);
    } else {
      alert(data.message || 'Could not find a snack matching your filters.')
    }
  } catch (error) {
    console.error('Failed to add snack:', error)
  }
}

// ---------------------------------------------------------
// NEW RECIPE CREATION LOGIC
// ---------------------------------------------------------

const newRecipe = ref({
  name: 'Grandmas Test Lasagna',
  instructions: '1. Boil water.\n2. Cook pasta.\n3. Bake for 30 mins.',
  prep_time_minutes: 45,
  is_public: 0,
  calories: 650,
  protein: 30,
  fat: 20,
  carbs: 60,
  meal_types: ['dinner', 'lunch'],
  ingredients: [
    { id: 1, amount: 200, unit: 'grams' }, 
    { id: 2, amount: 1, unit: 'cup' }      
  ],
  imageFile: null
})

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    newRecipe.value.imageFile = file
  }
}

const testCreateRecipe = async () => {
  const formData = new FormData()

  formData.append('name', newRecipe.value.name)
  formData.append('instructions', newRecipe.value.instructions)
  formData.append('prep_time_minutes', newRecipe.value.prep_time_minutes)
  formData.append('is_public', newRecipe.value.is_public)
  formData.append('calories', newRecipe.value.calories)
  formData.append('protein', newRecipe.value.protein)
  formData.append('fat', newRecipe.value.fat)
  formData.append('carbs', newRecipe.value.carbs)

  if (newRecipe.value.imageFile) {
    formData.append('image', newRecipe.value.imageFile)
  }

  newRecipe.value.meal_types.forEach((type, index) => {
    formData.append(`meal_types[${index}]`, type)
  })

  newRecipe.value.ingredients.forEach((ingredient, index) => {
    formData.append(`ingredients[${index}][id]`, ingredient.id)
    formData.append(`ingredients[${index}][amount]`, ingredient.amount)
    if (ingredient.unit) {
      formData.append(`ingredients[${index}][unit]`, ingredient.unit)
    }
  })

  try {
    const response = await $fetch(`https://projekt.ddev.site:33001/api/user/recipes`, {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
        'Accept': 'application/json'
      },
      body: formData
    })
    
    console.log('Recipe Created successfully:', response.data)
    alert('Success! Custom recipe created.')
    
    // Automatically refresh your custom recipe list to see the image immediately
    fetchUserRecipes()
    
  } catch (error) {
    console.error('Failed to create recipe:', error.data || error)
    alert('Failed to create recipe. Check console for validation errors.')
  }
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://via.placeholder.com/150?text=No+Image' // Safe fallback

  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }

  return `https://projekt.ddev.site:33001/storage/${imagePath}`
}

// ---------------------------------------------------------
// SETTINGS VERIFICATION LOGIC
// ---------------------------------------------------------
const fetchedSettings = ref(null)

// Reactive object tied directly to the HTML form
const settingsForm = ref({
  daily_calorie_target: 2000,
  prep_time_preference: 45,
  goals: [],
  meal_plan_preference: [],
  disliked_ingredient_ids: []
})

const testFetchSettings = async () => {
  try {
    const response = await $fetch(`https://projekt.ddev.site:33001/api/user/settings`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
        'Accept': 'application/json'
      }
    })
    
    // Store raw data to unhide the form
    fetchedSettings.value = response.data
    
    // Inject the fetched database values into the interactive UI form
    settingsForm.value = {
      daily_calorie_target: response.data.settings.daily_calorie_target,
      prep_time_preference: response.data.settings.prep_time_preference,
      goals: response.data.settings.goals || [],
      meal_plan_preference: response.data.settings.meal_plan_preference || [],
      disliked_ingredient_ids: response.data.disliked_ingredients.map(i => i.id) || []
    }
    
  } catch (error) {
    console.error('Failed to fetch settings:', error.data || error)
    alert('Failed to fetch settings. Check console.')
  }
}

const testUpdateSettings = async () => {
  try {
    // Send the dynamic form data instead of the hardcoded dummy payload
    const response = await $fetch(`https://projekt.ddev.site:33001/api/user/settings`, {
      method: 'PUT',
      headers: {
        'Authorization': 'Bearer 2|NZrAERd5f06LUtLGU3lntG9XL0cQKeFetiwzaD3A97d08772',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: settingsForm.value
    })
    
    alert('Settings updated successfully!')
    
  } catch (error) {
    console.error('Failed to update settings:', error.data || error)
    alert('Failed to update settings. Check console for validation errors.')
  }
}
</script>