angular.module('AdelantoViaticos', [])
    .controller('AdelantoViaticosController', ['$scope', '$location', 'adelantoViaticos', function ($scope, $location, adelantoViaticos) {

        $scope.locationPath = $location.path();
        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true};

        console.log(adelantoViaticos);
        $scope.adelantoViaticos = adelantoViaticos.data.adelantoViaticos;
        $scope.columns          = adelantoViaticos.data.columns;

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