export interface Category {
  id: number;
  name: string;
  slug: string;
  default_shelf_life_days?: number;
}

export interface Ingredient {
  id: number;
  name: string;
  category?: Category;
  base_unit?: string;
  emoji?: string;
}

export interface InventoryItem {
  id: number;
  user_id: number;
  ingredient_id: number;
  amount_left: number | null;
  unit: string;
  status: 'FULL' | 'OPENED' | 'LOW';
  expiration_date: string | null;
  is_frozen: boolean;
  ingredient: Ingredient;
}
