## Server side processing for ng-smart-table for Laravel 5

This package provides services for server-side processing data for [angular-smart-table](https://lorenzofox3.github.io/smart-table-website/)
### Installation

1. Install via composer
    ```
        composer require mikelmi/mks-theme:dev-master
    ```
2. Add the service provider in `config/app.php`, to `providers`:
    ```
        Mikelmi\SmartTable\Providers\SmartTableServiceProvider::class,
    ```
3. Publish config
    ```    
        php artisan vendor:publish --provider="Mikelmi\SmartTable\Providers\SmartTableServiceProvider"
    ```

### Usage
#### Server side
Create route for handling smart-table-request
Inside the route:
1. Create data source. It can be Query Builder (Eloquent/Database) or Collection

    ```php
    //Query Builder
    $source = \App\User::select(['id', 'name', 'email', 'created_at']);
    
    //or Collection
    $source = collect([
        ['id' => '1', 'name' => 'John Smith'],
        ['id' => '2', 'name' => 'Mister X'],
        //...
    ]);
    ```
2. Create SmartTable Engine instance
    ```php
    $engine = app(\Mikelmi\SmartTable\SmartTable::class)->make($source);
    
    //optional set columns for general search
    $engine->setSearchColumns(['name', 'email'])
    ```

3. Apply request
    ```php
    $engine->apply();
    
    //optional apply advanced method for your source, e.g. default sorting
    $engine->orderBy('created_at', 'desc');
    ```

4. Return the response
    ```php
    return $engine->response();
    ```

#### Client side
1. Include javascript file. This package ships with three js files:
    
    * `mks-smart-table.js` - contains only base functionality, without any libraries
    * `mks-smart-table-st.js` - includes angular-smart-table
    * `mks-smart-table-full.js` - includes all required dependencies (angular 1.x and angular-smart-table)
    
    E.g. (in your template):
    
    ```html
       ...
       <script src="path/to/angular.js"></script>
       <script src="{{asset('vendor/mikelmi/mks-smart-table/js/mks-smart-table-st.js')}}"></script>
       <!-- Your another js -->
    </body>
    ```

2. Add `mks-smart-table` dependency to your angular application. E.g.:
    ```javascript
    var app = angular.module('myApp', ['mks-smart-table']);
    ```

3. Init controller TableCtrl
    ```html
    <div ng-controller="TableCtrl as grid" ng-init="grid.init('{{url('/url/to/handler')}}')">
    ```

4. Output the table
    ```html
    <table class="table" st-pipe="grid.pipeServer" st-table="grid.rows">
       <tbody>
           <tr ng-repeat="row in grid.rows">
               <td>{[{row.id}]}</td>
               <td>{[{row.name}]}</td>
               <td>{[{row.email}]}</td>
               <td>{[{row.created_at}]}</td>
           </tr>
       </tbody>
    </table>
    ```

5. Advanced features:
    * **Global search**
    
        You can add some input outside the table for filtering the results
        ```html
        <input type="search" ng-model="gridQuery">
        ```
        And then add `mst-watch-query` directive to the table head
        ```html
        <thead mst-watch-query="gridQuery">
        ```
    
    * **Select rows**
        
        ```html
          <tbody>
              <tr ng-repeat="row in grid.rows">
                  <td mst-select-row="row"></td>
        ```
        
    * **'Select All' checkbox**
    
        ```html
        <thead>
            <tr>
                <th mst-select-all-rows="grid.rows"> </th>
        ```
        
    * **TableCtrl functions**
        * `removeLocalRow(row)` - remove row without server-side action
        * `removeRow(row, url, confirmText)` - remove row after server-side action by url
        * `removeSelected(url, confirmText)` - remove selected rows after server-side action by url
        * `getSelected()` - get selected rows
        * `updateRow(row, url, confirmText)` - post row by url and update it with returned data
        * `updateSelected(url, confirmText)` - same as `updateRow` but works with array of selected rows

### Additional Info

There is no styles for the tables in this package. You can [Twitter Bootstrap](http://getbootstrap.com/) for styling it.
 
Here https://github.com/mikelmi/mks-smart-table/tree/master/example you can check the example of usage this package.

For more information about client-side implementation please visit the angular-smart-table site - https://lorenzofox3.github.io/smart-table-website/  