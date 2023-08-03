let mix = require('laravel-mix')

require('./mix')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({version: 3})
    .postCss('resources/css/field.css', 'css', [
        require("tailwindcss"),
    ])
    .nova('letsgoi/nova-attach-many')
