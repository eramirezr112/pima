angular.module('Home', ['ui.bootstrap', 'angularUtils.directives.dirPagination'])
    .controller('HomeController', ['$scope', '$http', 'usuario', '$location', function($scope, $http, usuario, $location) {

        $scope.locationPath = $location.path();
        $scope.codEmpleado = usuario.data.usuario.COD_EMPLEADO.trim();
                
    }]);