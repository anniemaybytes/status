const js = require('@eslint/js');
const globals = require('globals');
const eslintConfigPrettier = require('eslint-config-prettier');

module.exports = [
  {
    rules: {
      // external
      ...js.configs.recommended.rules,
      ...eslintConfigPrettier.rules,

      // overrides
      'max-len': ['error', { code: 200 }]
    },
    languageOptions: {
      globals: {
        ...globals.es2015
      }
    }
  },
  {
    files: ['assets/**/*.js'],
    rules: {
      'no-console': 'error'
    },
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.jquery
      }
    }
  },
  {
    files: ['*.config.cjs'],
    rules: {
      'no-console': 'off'
    },
    languageOptions: {
      globals: {
        ...globals.node
      }
    }
  }
];
