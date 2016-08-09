var elixir = require('laravel-elixir');

var path = {
    node: 'node_modules/',
    node_js: '../../../node_modules/'
};

elixir(function(mix) {
    mix.scripts([
        path.node_js + 'angular-smart-table/dist/smart-table.js',
        '*.js'
    ], 'public/js/mks-smart-table.js');
});