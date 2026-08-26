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
            <li v-for="(meal, index) in dayData.meals" :key="meal.id" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 10px 0;">
              <div>
                <strong>{{ meal.title || meal.name }}</strong> 
                <br>
                <span style="font-size: 0.9em; color: #666;">
                  Calories: {{ meal.calories }} | Prep Time: {{ meal.prep_time_minutes }} mins
                </span>
              </div>
              
              <!-- Single Meal Regenerate Button -->
              <button @click="regenerateSingleMeal(dayName, index)" title="Regenerate this meal" style="background: #e2e8f0; border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <span class="material-symbols-outlined" style="font-size: 18px;">🔄</span>
              </button>
            </li>
          </ul>

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
// EXISTING MEAL PLAN & COOKING LOGIC
// ---------------------------------------------------------
const generationFailed = ref(false)
const errorMessage = ref('')
const errorSummary = ref(null)
const weeklyPlan = ref(null)
const targetCalories = ref(0)
const cookingFailed = ref(false)
const summary = ref({ have: [], missing: [] })

const testGenerateMealPlan = async () => {
    generationFailed.value = false
    weeklyPlan.value = null
    errorMessage.value = ''
    errorSummary.value = null

    try {
        const response = await $fetch(`https://projekt.ddev.site:33001/api/meal-plan/generate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer 2|AIliMDNwDB1LqatPrNoMOgCed7MpIlceHQdP51v35df8215b',
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
                'Authorization': 'Bearer 2|AIliMDNwDB1LqatPrNoMOgCed7MpIlceHQdP51v35df8215b',
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
                'Authorization': 'Bearer 2|AIliMDNwDB1LqatPrNoMOgCed7MpIlceHQdP51v35df8215b',
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
  const typeMap = { 0: 'breakfast', 1: 'lunch', 2: 'dinner' }
  const mealType = typeMap[mealIndex]

  try {
    const response = await fetch('https://projekt.ddev.site:33001/api/meal-plan/regenerate-meal', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer 2|AIliMDNwDB1LqatPrNoMOgCed7MpIlceHQdP51v35df8215b'
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

// ---------------------------------------------------------
// NEW RECIPE CREATION LOGIC
// ---------------------------------------------------------

// Reactive object holding dummy data for quick testing
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

// Grabs the actual file when the user selects one
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    newRecipe.value.imageFile = file
  }
}

const testCreateRecipe = async () => {
  const formData = new FormData()

  // Append standard text fields
  formData.append('name', newRecipe.value.name)
  formData.append('instructions', newRecipe.value.instructions)
  formData.append('prep_time_minutes', newRecipe.value.prep_time_minutes)
  formData.append('is_public', newRecipe.value.is_public)
  formData.append('calories', newRecipe.value.calories)
  formData.append('protein', newRecipe.value.protein)
  formData.append('fat', newRecipe.value.fat)
  formData.append('carbs', newRecipe.value.carbs)

  // Append the Image File (if one was selected)
  if (newRecipe.value.imageFile) {
    formData.append('image', newRecipe.value.imageFile)
  }

  // Append Arrays (meal_types)
  newRecipe.value.meal_types.forEach((type, index) => {
    formData.append(`meal_types[${index}]`, type)
  })

  // Append Array of Objects (ingredients)
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
        'Authorization': 'Bearer 2|AIliMDNwDB1LqatPrNoMOgCed7MpIlceHQdP51v35df8215b',
        'Accept': 'application/json'
      },
      body: formData
    })
    
    console.log('Recipe Created successfully:', response.data)
    alert('Success! Check console for the created recipe data.')
    
  } catch (error) {
    console.error('Failed to create recipe:', error.data || error)
    alert('Failed to create recipe. Check console for validation errors.')
  }
}
</script>