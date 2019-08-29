let mix = require('laravel-mix');

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
    .copyDirectory('resources/assets/fonts', 'public/fonts');

mix.styles([
    'node_modules/bootstrap4-duallistbox/dist/bootstrap-duallistbox.min.css',
    'node_modules/select2/dist/css/select2.min.css'
], 'public/css/vendor.css');

mix.scripts([
    'node_modules/bootstrap4-duallistbox/dist/jquery.bootstrap-duallistbox.min.js',
    'node_modules/select2/dist/js/select2.min.js'
], 'public/js/vendor.js');

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}
