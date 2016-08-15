(function(){

    var app = angular.module('mks-smart-table', ['smart-table']);

    app.run(['$templateCache',
        function ($templateCache) {
            $templateCache.put('template/smart-table/pagination.html',
                '<nav ng-if="numPages && pages.length >= 2" aria-label="Page navigation">' +
                '<ul class="pagination">' +
                '<li class="page-item"><a class="page-link" href="javascript: void(0);" ng-click="selectPage(1)"><span>&laquo;</span></a></li>' +
                '<li class="page-item" ng-repeat="page in pages" ng-class="{active: page==currentPage}"><a class="page-link" href="javascript: void(0);" ng-click="selectPage(page)">{{page}}</a></li>' +
                '<li class="page-item"><a class="page-link" href="javascript: void(0);" ng-click="selectPage(numPages)"><span>&raquo;</span></a></li>' +
                '</ul></nav>');
        }
    ]);

    app.controller('TableCtrl', ['$http', '$q', '$scope', '$filter',
        function($http, $q, $scope, $filter) {

            this.rows = [];
            this.url = null;
            this.start = 0;
            this.end = 0;
            this.total = 0;
            this.hasSelected = 0;
            this.idKey = 'id';

            var self = this;

            var canceler = null;

            this.init = function(url, idKey) {
                this.url = url;
                if (idKey) {
                    this.idKey = idKey;
                }
            };

            function getPage(url, start, number, params) {
                if (canceler) {
                    canceler.resolve('cancel');
                }
                canceler = $q.defer();

                var data = {
                    start: start,
                    number: number
                };

                if (params.sort) {
                    data.sort = params.sort;
                }

                if (params.search && params.search.predicateObject) {
                    data.search = params.search.predicateObject;
                }

                var req = {
                    'method': 'GET',
                    'url': url + '?' + jQuery.param(data),
                    'headers': {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    'timeout': canceler.promise
                };
                return $http(req);
            }

            this.pipeServer = function(tableState) {
                if (!self.url) {
                    return;
                }

                self.isLoading = true;

                var pagination = tableState.pagination;

                var start = pagination.start || 0;
                var number = pagination.number || 10;

                getPage(self.url, start, number, tableState).success(function(result) {
                    self.rows = result.data;
                    tableState.pagination.numberOfPages = result.pages;

                    var start = tableState.pagination.start;

                    self.end = start + self.rows.length;

                    if (self.rows.length > 0) {
                        start++;
                    }

                    self.start = start;
                    self.total = result.total;
                    self.hasSelected = 0;
                    self.isLoading = false;
                }).error(function() {
                    self.isLoading = false;
                });
            };

            function removeLocalRow(row) {
                var index = self.rows.indexOf(row);
                if (index !== -1) {
                    self.rows.splice(index, 1);
                    if (self.total > 0) {
                        self.total--;
                    }
                    if (row.isSelected && self.hasSelected > 0) {
                        self.hasSelected--;
                    }
                }
            }

            this.removeRow = function(row, url, confirmText) {
                if (confirmText && !confirm(confirmText)) {
                    return false;
                }

                if (!url) {
                    removeLocalRow(row);
                    return false;
                }

                row.isLoading = true;
                $http.post(url).then(function(){
                    row.isLoading = false;
                    removeLocalRow(row);
                }, function() {
                    row.isLoading = false;
                });

                return false;
            };

            this.getSelected = function() {
                return $filter('filter')(this.rows, {isSelected: true});
            };

            function rowIdentities(arr) {
                return jQuery.map(arr, function(val) {
                    return val[self.idKey];
                });
            }

            this.removeSelected = function(url, confirmText) {
                if (confirmText && !confirm(confirmText)) {
                    return false;
                }

                var selected = this.getSelected();

                if (!selected.length) {
                    return false;
                }

                if (!url) {
                    angular.forEach(selected, function(row) {
                        removeLocalRow(row);
                    });
                    return false;
                }

                self.isLoading = true;
                $http.post(url, {id: rowIdentities(selected)}).then(function(){
                    angular.forEach(selected, function(row) {
                        removeLocalRow(row);
                    });
                    self.isLoading = false;
                }, function() {
                    self.isLoading = false;
                });
            };

            $scope.$on('row-selected', function(e, selected) {
                if (selected) {
                    self.hasSelected++;
                } else if(self.hasSelected > 0) {
                    self.hasSelected--;
                }
            });

            this.updateRow = function(row, url, confirmText) {
                if (confirmText && !confirm(confirmText)) {
                    return false;
                }

                row.isLoading = true;
                $http.post(url).then(function(response){
                    row.isLoading = false;
                    var data = response.data;
                    if (data && data.model) {
                        angular.extend(row, data.model);
                    }
                }, function() {
                    row.isLoading = false;
                });

                return false;
            };

            this.updateSelected = function(url, confirmText) {
                if (confirmText && !confirm(confirmText)) {
                    return false;
                }

                var selected = this.getSelected();

                if (!selected.length) {
                    return false;
                }

                self.isLoading = true;

                $http.post(url, {id: rowIdentities(selected)}).then(function(response){
                    var data = response.data;
                    if (data && data.models) {
                        angular.forEach(selected, function (row) {
                            if (typeof data.models[row[self.idKey]] != 'undefined') {
                                angular.extend(row, data.models[row[self.idKey]]);
                            }
                        });
                    }
                    self.isLoading = false;
                }, function() {
                    self.isLoading = false;
                });
            };
        }
    ]);

    // smart-table external search
    app.directive('mstWatchQuery', [function() {
        return {
            restrict: 'A',
            require:'^stTable',
            scope:{
                mstWatchQuery:'='
            },
            'link': function(scope, el, attr, ctrl) {
                scope.$watch('mstWatchQuery',function(val){
                    ctrl.search(val);
                });
            }
        }
    }]);

    // smart-table checkbox for selecting row
    app.directive('mstSelectRow', [function() {
        return {
            restrict: 'EA',
            template: '<i class="text-muted fa fa-square-o st-chk"></i>',
            scope: {
                row: '=mstSelectRow'
            },
            link: function (scope, element) {

                if (scope.row.non_selectable) {
                    return false;
                }

                element.on('click', function (evt) {
                    evt.preventDefault();
                    scope.$apply(function () {
                        scope.row.isSelected = !(scope.row.isSelected||0);
                    });
                });

                scope.$watch('row.isSelected', function (newValue, oldValue) {
                    element.parent().toggleClass('st-selected table-info', newValue == true);
                    element.children().toggleClass('text-muted fa-square-o', newValue !== true)
                        .toggleClass('fa-check-square st-checked', newValue == true);

                    scope.$emit('row-selected', newValue);
                });
            }
        }
    }]);

    // smart-table checkbox for selecting all rows
    app.directive('mstSelectAllRows', [function() {
        return {
            restrict: 'EA',
            template: '<i class="text-muted fa fa-square-o st-chk"></i>',
            scope: {
                all: '=mstSelectAllRows'
            },
            link: function (scope, element) {
                element.on('click', function (evt) {
                    evt.preventDefault();
                    scope.$apply(function () {
                        scope.isAllSelected = !scope.isAllSelected||false;
                    });
                });

                scope.$watch('isAllSelected', function () {
                    if (!scope.all) {
                        return;
                    }
                    scope.all.forEach(function (val) {
                        if (!val.non_selectable) {
                            val.isSelected = scope.isAllSelected || false;
                        }
                    });

                    element.children().toggleClass('text-muted fa-square-o', scope.isAllSelected !== true)
                        .toggleClass('fa-check-square st-checked', scope.isAllSelected == true);
                });

                scope.$watch('all', function (newVal, oldVal) {
                    scope.isAllSelected = false;
                });
            }
        }
    }]);

})(window.angular);
