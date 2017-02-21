<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<head>
    <meta charset="UTF-8">
    <title>Smart Table demo</title>

    <!-- Twitter Bootstrap 3 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <style>
        /* styles for sorting columns */
        .st-sortable {
            cursor: pointer;
        }
        .st-sort-ascent:before{
            content: '\25B2';
            color: #aaaaaa;
        }
        .st-sort-descent:before{
            content: '\25BC';
            color: #aaaaaa;
        }
    </style>
</head>
<body ng-controller="TableCtrl as grid" ng-init="grid.init('{{url('/smart-table/handler')}}')">

<h3>Users</h3>

<div class="form-inline">
    <div class="input-group">
        <span class="input-group-addon">Global search</span>
        <input type="search" class="form-control" placeholder="Search..." ng-model="gridQuery">
    </div>
</div>

<table class="table table-grid table-hover table-sm" st-pipe="grid.pipeServer" st-table="grid.rows">
    <thead mst-watch-query="gridQuery">
    <tr class="thead-default">
        <th mst-select-all-rows="grid.rows"> </th><!-- 'Select All' checkbox -->
        <th st-sort="id" class="st-sortable">#</th>
        <th st-sort="name" class="st-sortable">Name</th>
        <th st-sort="email" class="st-sortable">Email</th>
        <th st-sort="users.created_at" class="st-sortable">Created at</th>
        <th><!-- actions --></th>
    </tr>
    <tr>
        <th><!-- checkbox --></th>
        <th><!-- id --></th>
        <th><!-- name -->
            <input st-search="name" data-placeholder="Name" class="form-control form-control-sm form-block" type="search"/>
        </th>
        <th><!-- email -->
            <input st-search="email" data-placeholder="Email" class="form-control form-control-sm form-block" type="search"/>
        </th>
        <th><!-- created_at -->
            <input st-search="created_at" data-placeholder="Created at" class="form-control form-control-sm form-block" type="date"/>
        </th>
        <th class="st-actions-th"><!-- actions --></th>
    </tr>
    </thead>

    <tbody>
    <tr ng-repeat="row in grid.rows">
        <td mst-select-row="row"></td>
        <td>{[{row.id}]}</td>
        <td>{[{row.name}]}</td>
        <td>{[{row.email}]}</td>
        <td>{[{row.created_at}]}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-primary btn-xs" href="#/user/edit/{[{row.id}]}" title="Edit"><i class="glyphicon glyphicon-pencil"></i></a>
                <button class="btn btn-danger btn-xs" ng-click="grid.removeRow(row,'user/remove/'+row.id,'Confirm message')" title="Delete">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </td>
    </tr>
    </tbody>

    <tfoot>

    <tr>
        <td colspan="6">
            <div class="pull-left text-muted">
                {[{ grid.start }]} - {[{ grid.end }]} / {[{ grid.total }]}<br />
                Selected: {[{ grid.hasSelected }]}
            </div>
            <div class="pull-right" st-pagination="" st-items-by-page="10"></div>
            <div class="clearfix"></div>
        </td>
    </tr>
    </tfoot>
</table>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Twitter Bootstrap 3 -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!-- Mks-Smart-Table (including angular and ng-smart-table) -->
<script src="{{asset('vendor/mikelmi/mks-smart-table/js/mks-smart-table-full.js')}}"></script>

<script>
    var app = angular.module('myApp', ['mks-smart-table']);

    app.config(["$interpolateProvider", function ($interpolateProvider) {
        $interpolateProvider.startSymbol('{[{');
        $interpolateProvider.endSymbol('}]}');
    }]);
</script>

</body>
</html>