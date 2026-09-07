export interface Meal {
  id: number;
  title: string;
  calories: number;
  prepTime: number;
  imageUrl: string;
  imageAlt: string;
  isPrepared: boolean;
  isFavorite?: boolean;
}
