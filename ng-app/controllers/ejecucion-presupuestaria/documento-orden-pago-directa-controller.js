angular.module('DocumentoOrdenPagoDirecta', ['ui.bootstrap'])
    .controller('DocumentoOrdenPagoDirectaController', ['$scope', 'ordenData', '$uibModalInstance', function($scope, ordenData, $uibModalInstance) {

		$scope.orden = ordenData.data.orden[0];
    	$scope.title = "Orden de Pago Directa: " + $scope.orden.COD_ORDEN;
    	
    	$scope.orden.detalle = ordenData.data.detalle;

        $scope.close = function () {
            $uibModalInstance.close();
        };

    }]);    