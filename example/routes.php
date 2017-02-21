<?php

Route::get('/smart-table', function () {
    return view('smart-table');
});

Route::get('/smart-table/handler', function (\Mikelmi\SmartTable\SmartTable $smartTable) {

    /**
     * source for SmartTable can be Collection or Query Builder
     */

    /**
     * Example source form Eloquent Query Builder
     */
    /*$source = \App\User::select([
        'id',
        'name',
        'email',
        'created_at',
    ]);*/

    /**
     * Example source as simple Collection
     */
    $items = json_decode(file_get_contents(__DIR__.'/sample-data.json'), true);
    $source = collect($items);

    return $smartTable->make($source)
        ->setSearchColumns(['name', 'email']) //For global search
        ->apply()
        //->orderBy('created_at', 'desc') //optional default order for Query Builder
        //->sortBy('created_at', true) //optional default order for Collection
        ->response();
});