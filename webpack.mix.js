let mix = require('laravel-mix')

require('./mix')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .nova("dillingham/nova-attach-many")
