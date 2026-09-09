export interface MacroTarget {
  current: number;
  target: number;
}

export interface InventoryItem {
  id: number;
  expiration_date: string;
}

export interface CustomPageProps {
  auth: {
    theme?: string;
    inAppAlerts?: boolean;
    expiringCount?: number;
  };
  expiringAlerts?: {
    expired?: InventoryItem[];
    critical?: InventoryItem[];
    urgent?: InventoryItem[];
  };
  flash?: {
    success?: string;
  };
  topbarData?: {
    macros: {
      calories: MacroTarget;
      protein: MacroTarget;
      carbs: MacroTarget;
      fat: MacroTarget;
    } | null;
    mealsCooked: {
      current: number;
      total: number;
    };
  };
}
