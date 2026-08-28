export interface PlannerMeal {
  id: number;
  meal_type: string;
  name: string;
  calories: number;
  image?: string;
  prep_time_minutes?: number;
  diets?: string[];
  isPinned?: boolean;
  isRolling?: boolean;
}

export interface DayPlan {
  total_calories: number;
  has_snack: boolean;
  perfect_match: boolean;
  meals: PlannerMeal[];
}
