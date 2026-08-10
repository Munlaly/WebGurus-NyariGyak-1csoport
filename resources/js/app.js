import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import ui from '@nuxt/ui/vue-plugin';
import { createRouter, createMemoryHistory } from 'vue-router';

// Blank memory router just to satisfy NuxtUi
const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    {
      path: '/:pathMatch(.*)*',
      component: { render: () => null },
    },
  ],
});

router.push('/');

createInertiaApp({
  // Tell Inertia where Vue pages are
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(router)
      .use(ui);

    // WAIT for the router promise to resolve before mounting the app
    // Needed just for Nuxt Ui warning
    router.isReady().then(() => {
      app.mount(el);
    });
  },
});
