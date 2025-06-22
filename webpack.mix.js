let mix = require('laravel-mix')
let NovaExtension = require('laravel-nova-devtool')

mix.extend('nova', new NovaExtension())

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3 })
  .sass('resources/css/tool.scss', 'dist/css')
  .nova('bencolmer/nova-resource-hierarchy')
  .version()
