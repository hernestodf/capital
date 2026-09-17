import js from '@eslint/js';
import globals from 'globals';

export default [
  js.configs.recommended,
  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        BASE_URL: 'readonly',
        CSRF_TOKEN: 'readonly',
        EVENTO_ID: 'readonly',
        SALAS_MAP: 'readonly',
        showToast: 'readonly',
        showToastIfNoError: 'readonly',
        escapeHtml: 'readonly',
      },
    },
    rules: {
      'no-var': 'error',
      'prefer-const': 'error',
      'prefer-arrow-callback': 'error',
      'arrow-spacing': 'error',
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      'no-console': 'warn',
    },
  },
  {
    ignores: ['public/dist/**', 'public/js/**', 'node_modules/**'],
  },
];
