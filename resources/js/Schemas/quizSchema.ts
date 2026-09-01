import { z } from 'zod';

export const stepGoalSchema = z.object({
  fitness_goal: z.enum(['lose_weight', 'maintain', 'gain_muscle'] as const, {
    message: 'Please seelct a valid fitness goal',
  }),
});

export const stepDietSchema = z.object({
  meal_plan_preferences: z
    .array(z.number().int().positive())
    .min(1, 'Please select at least one dietary preference.'),
});

export const stepDislikedIngredientsSchema = z.object({
  disliked_ingredients: z.array(z.number().int().positive()),
});

export const stepMetabolismSchema = z.object({
  sex: z.enum(['male', 'female'] as const, {
    message: 'Please select your sex',
  }),
  birthdate: z
    .string()
    .date('Please enter a valid birthdate (YYYY-MM-DD)')
    .refine(
      (val) => {
        const date = new Date(val);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const hundredYearsAgo = new Date(today);
        hundredYearsAgo.setFullYear(today.getFullYear() - 100);

        const oneYearAgo = new Date(today);
        oneYearAgo.setFullYear(today.getFullYear() - 1);

        return date >= hundredYearsAgo && date < oneYearAgo;
      },
      {
        message:
          'Birthdate must be more than 1 year ago and within the last 100 years.',
      },
    ),
  height_cm: z.coerce.number().positive('Height must be a positive number'),
  weight_kg: z.coerce.number().positive('Weight must be a positive number'),
  baseline_activity: z.enum(
    [
      'sedentary',
      'lightly_active',
      'moderately_active',
      'very_active',
    ] as const,
    {
      message: 'Please seect your baseline activity',
    },
  ),
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

const exerciseLevelEnum = z.enum(['rest', 'moderate', 'heavy'], {
  message: 'Invalid exercise level selected',
});

export const stepExerciseSchema = z.object({
  exercise_schedule: z.object({
    monday: exerciseLevelEnum,
    tuesday: exerciseLevelEnum,
    wednesday: exerciseLevelEnum,
    thursday: exerciseLevelEnum,
    friday: exerciseLevelEnum,
    saturday: exerciseLevelEnum,
    sunday: exerciseLevelEnum,
  }),
});

export const quizFormSchema = stepGoalSchema
  .merge(stepDietSchema)
  .merge(stepDislikedIngredientsSchema)
  .merge(stepMetabolismSchema)
  .merge(stepHouseholdSchema)
  .merge(stepPrepTimeSchema)
  .merge(stepExerciseSchema);

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
