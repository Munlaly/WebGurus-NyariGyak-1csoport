import { ref, watch } from 'vue';

export function useIngredientSearch() {
  const searchTerm = ref('');
  const searchResults = ref<{ id: number; name: string }[]>([]);
  const isSearchLoading = ref(false);
  const isDropdownOpen = ref(false);

  let searchTimeout: ReturnType<typeof setTimeout>;
  let searchAbortController: AbortController | null = null;

  watch(searchTerm, (query) => {
    clearTimeout(searchTimeout);

    if (!query) {
      searchResults.value = [];
      isDropdownOpen.value = false;
      return;
    }

    searchTimeout = setTimeout(async () => {
      if (searchAbortController) {
        searchAbortController.abort();
      }

      searchAbortController = new AbortController();
      isSearchLoading.value = true;
      isDropdownOpen.value = true;

      try {
        const response = await fetch(
          route('ingredients.search', { q: query }),
          {
            signal: searchAbortController.signal,
          },
        );
        if (!response.ok) {
          throw new Error('Search Failed');
        }
        const data = await response.json();
        searchResults.value = data;
      } catch (error: unknown) {
        if (error instanceof Error && error.name !== 'AbortError') {
          console.error('Ingredient search error:', error);
          searchResults.value = [];
        }
      } finally {
        isSearchLoading.value = false;
      }
    }, 300);
  });

  return {
    searchTerm,
    searchResults,
    isSearchLoading,
    isDropdownOpen,
  };
}
