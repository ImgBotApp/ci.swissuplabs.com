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

mix.sass('resources/assets/sass/app.scss', 'public/css')
    .options({
        postCss: [
            require('cssnano')({
                preset: ['default', {
                    discardComments: {
                        removeAll: true
                    }
                }]
            })
        ]
   });

mix.scripts([
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/imperavi-kube/dist/js/kube.min.js',
    'node_modules/js-cookie/src/js.cookie.js',
    'resources/assets/js/app.js'
], 'public/js/app.js').version();
