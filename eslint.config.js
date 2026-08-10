import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import prettierConfig from 'eslint-config-prettier';

export default [
  // 1. Base JavaScript rules
  js.configs.recommended,

  // 2. Vue 3 rules (this automatically tells ESLint to check .vue files!)
  ...pluginVue.configs['flat/recommended'],

  // 3. Turn off anything that conflicts with Prettier
  prettierConfig,

  // 4. Custom team overrides
  {
    languageOptions: {
      // globals for inertia
      globals: {
        route: 'readonly',
        $page: 'readonly',
      },
    },
    rules: {
      'no-unused-vars': 'error',
      'no-console': 'off',
      'vue/multi-word-component-names': 'off',
    },
  },
];
