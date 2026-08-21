import { z } from 'zod';

export const stepGoalSchema = z.object({
  goals: z.array(z.string()).min(1, 'Please select at least one goal'),
});

export const stepDietSchema = z.object({
  meal_plan_preferences: z.array(z.string()),
});

export const stepDislikedIngredientsSchema = z.object({
  disliked_ingredients: z.array(z.number().int().positive()),
});

export const stepMetabolismSchema = z.object({
  sex: z.enum(['male', 'female'] as const, {
    message: 'Please select your sex',
  }),
  birthdate: z.string().date('Please enter a valid birthdate (YYYY-MM-DD)'),
  height_cm: z.coerce.number().positive('Height must be a positive number'),
  weight_kg: z.coerce.number().positive('Weight must be a positive number'),
  baseline_activity: z
    .string()
    .min(1, 'Please select a baseline activity level'),
});

export const stepHouseholdSchema = z.object({
  household_size: z.coerce
    .number()
    .int()
    .min(1, 'Please select your household size'),
});

export const stepPrepTimeSchema = z.object({
  prep_time_preference: z.coerce
    .number()
    .int()
    .min(1, 'Please select prep time preference'),
});

export const quizFormSchema = stepGoalSchema
  .merge(stepDietSchema)
  .merge(stepDislikedIngredientsSchema)
  .merge(stepMetabolismSchema)
  .merge(stepHouseholdSchema)
  .merge(stepPrepTimeSchema);

export type QuizFormData = z.infer<typeof quizFormSchema>;

export interface CategoryOption {
  id: number;
  name: string;
  icon: string;
  ingredients: {
    id: number;
    name: string;
  }[];
}
