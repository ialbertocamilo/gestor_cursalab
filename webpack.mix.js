const mix = require("laravel-mix");
mix.disableNotifications();

// const BundleAnalyzerPlugin = require("webpack-bundle-analyzer")
//     .BundleAnalyzerPlugin;

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

mix.js("resources/js/app.js", "public/js")
    .js(
        "resources/js/documentation_page/app.js",
        "public/js/documentation_page.js"
    )
    .sass("resources/sass/app.scss", "public/css")
    .sourceMaps();

// module.exports = {
//     plugins: [new BundleAnalyzerPlugin()]
// };
