import z from 'zod';
export const createDietaryRulesSchema = (baseDietIds: number[]) => {
  return z.object({
    activeDiets: z
      .array(z.number().int().positive())
      .min(1, 'Please select at least one dietary preference.')
      .superRefine((val, ctx) => {
        const selectedBaseDiets = val.filter((id) => baseDietIds.includes(id));
        if (selectedBaseDiets.length > 1) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message:
              'You cannot select conflicting baseline diets (e.g., Vegetarian and Omnivore). Please select only one.',
          });
        }
      }),
    dislikedIngredients: z.array(
      z.object({
        id: z.number(),
        label: z.string(),
      }),
    ),
  });
};

export type DietaryRulesFormData = z.infer<
  ReturnType<typeof createDietaryRulesSchema>
>;
