export interface Category {
  id: number;
  name: string;
  default_shelf_life_days?: number;
}

export interface Ingredient {
  id: number;
  name: string;
  category?: Category;
  emoji?: string;
  base_unit?: string;
}

export interface ShoppingListItem {
  id: number;
  user_id: number;
  ingredient_id: number;
  quantity: number;
  unit: string;
  is_checked: boolean;
  ingredient: Ingredient;
}
