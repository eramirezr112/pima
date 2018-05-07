angular.module('DocumentoEgreso', ['ui.bootstrap'])
    .controller('DocumentoEgresoController', ['$scope', 'egresoData', '$uibModalInstance', function($scope, egresoData, $uibModalInstance) {
    	
		$scope.egreso = egresoData.data.egreso[0];
    	$scope.title = "Documento Egreso: " + $scope.egreso.num_documento;
    	
    	$scope.egreso.detalle = egresoData.data.detalle;    	

        $scope.close = function () {
            $uibModalInstance.close();
        };

    }]);    