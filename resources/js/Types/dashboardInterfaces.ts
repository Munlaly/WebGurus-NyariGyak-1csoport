export interface Meal {
  id: number;
  meal_plan_id: number;
  title: string;
  calories: number;
  prepTime: number;
  imageUrl: string;
  imageAlt: string;
  isPrepared: boolean;
  isFavorite?: boolean;
}

export interface SearchResult {
  id: number;
  name: string;
  meal_types: string[];
  calories: number;
}
