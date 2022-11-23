/* eslint-disable @typescript-eslint/no-var-requires */
const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.tsx',
  ],

  theme: {
    extend: {
      colors: {
        'ticksift-bright': '#1eb3b3',
        'ticksift-light': '#0d4d4d',
        'ticksift-dark': '#001f1f',
        'ticksift-accent-light': '#5278cc',
        'ticksift-accent-dark': '#2e4372',
        'ticksift-black': '#000f0f',
      },
      fontFamily: {
        sans: ['Nunito', ...defaultTheme.fontFamily.sans],
      },
    },
  },

  plugins: [require('@tailwindcss/forms')],
};
