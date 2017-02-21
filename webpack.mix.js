const { mix } = require('laravel-mix');

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

mix.js('resources/assets/js/mks-smart-table.js', 'public/js');

mix.js('resources/assets/js/build-st.js', 'public/js/mks-smart-table-st.js');
mix.js('resources/assets/js/build-full.js', 'public/js/mks-smart-table-full.js');
