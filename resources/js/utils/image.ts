export function getImageUrl(
  path: string | null,
  fallback?: string,
): string | undefined {
  if (!path) return fallback;
  if (path.startsWith('http')) return path;
  if (path.startsWith('storage/')) return `/${path}`;
  return `/storage/${path}`;
}
