export interface SecurityProps {
  user: { username: string; email: string };
}

export interface BiometricsProps {
  profile: {
    sex: 'male' | 'female';
    birthdate: string;
    height_cm: number;
    weight_kg: number;
    baseline_activity:
      'sedentary' | 'lightly_active' | 'moderately_active' | 'very_active';
  };
}

export interface TargetsProps {
  profile: { fitness_goal: 'lose_weight' | 'maintain' | 'gain_muscle' };
  schedule: Record<
    | 'monday'
    | 'tuesday'
    | 'wednesday'
    | 'thursday'
    | 'friday'
    | 'saturday'
    | 'sunday',
    'rest' | 'moderate' | 'heavy'
  >;
}

export interface RulesProps {
  activeDiets: number[];
  dislikedIngredients: { id: number; label: string }[];
  availableDietOptions: {
    id: number;
    name: string;
    description: string | null;
  }[];
}

export interface LogisticsProps {
  settings: {
    household_size: number;
    prep_time_preference: number;
  };
}

export interface SystemProps {
  settings: {
    system_preferences: {
      theme: 'light' | 'dark';
      pushNotifications: boolean;
      inAppAlerts: boolean;
      emailDigests: boolean;
    };
  };
}
