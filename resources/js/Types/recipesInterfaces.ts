export interface RecipeIngredient {
  id: number;
  name: string;
  pivot?: {
    amount: number;
    unit: string;
  };
}

export interface Recipe {
  id: number;
  name: string;
  image: string | null;
  calories: number;
  prep_time_minutes: number;
  protein: number;
  fat: number;
  carbs: number;
  meal_types: string[];
  is_public: boolean;
  instructions: string;
  ingredients: RecipeIngredient[];
}

export interface IngredientOption {
  id: number;
  name: string;
  base_unit: string;
  emoji: string | null;
}
