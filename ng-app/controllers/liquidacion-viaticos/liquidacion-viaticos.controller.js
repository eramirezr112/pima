angular.module('LiquidacionViaticos', [])
    .controller('LiquidacionViaticosController', ['$scope', '$location', 'liquidacionViaticos', function ($scope, $location, liquidacionViaticos) {

        $scope.locationPath = $location.path();
        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true};

        $scope.liquidacionViaticos = liquidacionViaticos.data.liquidacionViaticos;
        $scope.columns             = liquidacionViaticos.data.columns;

        // Se preparan las columnas a mostrar
        $scope.preparedColumns = [];
        angular.forEach($scope.columns, function(value, key) {            
            var newColumn = {
                visible: true,
                text: value
            };
            $scope.preparedColumns.push(newColumn);
        });        

    }]);