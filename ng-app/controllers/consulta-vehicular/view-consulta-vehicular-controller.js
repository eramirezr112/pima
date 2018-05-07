angular.module('ViewConsultaVehicular', ['ui.bootstrap'])
    .controller('ViewConsultaVehicularController', ['$scope', 'consultaData', '$uibModalInstance', function($scope, consultaData, $uibModalInstance) {

		$scope.consulta = consultaData.data.consulta;
    	$scope.title = "Consulta Vehicular";
    	
    	$scope.consulta.detalle = consultaData.data.detalle;

        $scope.tabs = [
            { title:'Datos Principales', content:$scope.reservado, templateUrl: '../ng-app/views/consulta-vehicular/tabs/datos-principales.html'},
            { title:'Contacto'         , content:$scope.aprobado , templateUrl: '../ng-app/views/consulta-vehicular/tabs/contacto.html'},
            { title:'Observaciones'    , content:$scope.ejecutado, templateUrl: '../ng-app/views/consulta-vehicular/tabs/observaciones.html'},
        ];

        $scope.close = function () {
            $uibModalInstance.close();
        };

    }]);    