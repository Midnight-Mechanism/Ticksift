const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copyDirectory('resources/assets/fonts', 'public/fonts')
    .styles([
        'node_modules/flatpickr/dist/themes/dark.css',
        'node_modules/select2/dist/css/select2.min.css',
        'node_modules/tabulator-tables/dist/css/tabulator_midnight.css'
    ], 'public/css/vendor.css')
    .scripts('resources/assets/js/stats.js', 'public/js/stats.js')
    .scripts([
        'node_modules/bootstrap4-duallistbox/dist/jquery.bootstrap-duallistbox.min.js',
        'node_modules/select2/dist/js/select2.min.js'
    ], 'public/js/vendor.js')
    .webpackConfig({
        plugins: [],
    });

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}
