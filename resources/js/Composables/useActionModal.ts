// resources/js/Composables/useActionModal.ts
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { FormDataType } from '@inertiajs/core';

export function useActionModal<TItem, TForm extends FormDataType<TForm>>(
  routeFn: (item: TItem) => string,
  initialForm: TForm,
  method: 'post' | 'put' = 'post',
) {
  const isOpen = ref(false);
  const selectedItem = ref<TItem | null>(null);
  const form = useForm(initialForm);

  const open = (item: TItem, customDefaults?: Partial<TForm>) => {
    selectedItem.value = item;
    form.defaults({ ...initialForm, ...customDefaults });
    form.reset();
    form.clearErrors();
    isOpen.value = true;
  };

  const submit = () => {
    const action = method === 'post' ? form.post : form.put;

    action(routeFn(selectedItem.value), {
      preserveScroll: true,
      onSuccess: () => {
        isOpen.value = false;
      },
    });
  };

  return reactive({
    isOpen,
    selectedItem,
    form,
    open,
    submit,
  });
}
