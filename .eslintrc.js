module.exports = {
  root: true,
  parser: '@typescript-eslint/parser',
  parserOptions: {
    ecmaVersion: 2020, // Use the latest ecmascript standard
    sourceType: 'module', // Allows using import/export statements
    ecmaFeatures: {
      jsx: true, // Enable JSX since we're using React
    },
  },
  settings: {
    'import/resolver': {
      typescript: {
        alwaysTryTypes: true,
        project: '.',
      },
    },
    react: {
      version: 'detect',
    },
  },
  env: {
    browser: true,
    amd: true,
    node: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:import/recommended',
    'plugin:react/recommended',
    'plugin:react-hooks/recommended',
    'plugin:prettier/recommended',
  ],
  plugins: ['@typescript-eslint', 'prettier'],
  ignorePatterns: ['public/build/', 'vendor'],
  rules: {
    'import/order': [
      1,
      {
        alphabetize: {
          order: 'asc',
          caseInsensitive: true,
        },
        groups: ['external', ['internal', 'parent', 'sibling', 'index'], 'unknown'],
        pathGroups: [
          {
            pattern: '{.,..}/*.css',
            group: 'index',
            position: 'after',
          },
        ],
        'newlines-between': 'always',
        warnOnUnassignedImports: true,
      },
    ],
    'react-hooks/exhaustive-deps': 'off',
    'react/jsx-first-prop-new-line': [2, 'multiline'],
    'react/jsx-max-props-per-line': [2, { maximum: 1, when: 'multiline' }],
    'react/jsx-indent-props': [2, 2],
    'react/jsx-closing-bracket-location': [2, 'tag-aligned'],
    'prettier/prettier': [
      'error',
      {},
      {
        usePrettierrc: true,
      },
    ],
    'react/react-in-jsx-scope': 'off',
    'react/prop-types': 'off',
    'no-console': 2,
    '@typescript-eslint/no-explicit-any': 'off',
    '@typescript-eslint/ban-ts-comment': 'off',
  },
  globals: {
    route: 'readonly',
    axios: 'readonly',
  },
};
