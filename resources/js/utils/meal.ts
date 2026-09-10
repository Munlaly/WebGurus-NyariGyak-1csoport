export function getMealPlaceholder(mealTypes?: string[]): string {
  if (!mealTypes || mealTypes.length === 0) return '🍲';
  const primaryType = mealTypes[0].toLowerCase();
  switch (primaryType) {
    case 'breakfast':
      return '🍳';
    case 'lunch':
      return '🥗';
    case 'dinner':
      return '🍝';
    case 'snack':
      return '🥨';
    default:
      return '🍽️';
  }
}
