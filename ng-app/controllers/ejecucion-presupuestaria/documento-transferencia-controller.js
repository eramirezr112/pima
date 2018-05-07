angular.module('DocumentoTransferencia', ['ui.bootstrap'])
    .controller('DocumentoTransferenciaController', ['$scope', 'transferenciaData', '$uibModalInstance', function($scope, transferenciaData, $uibModalInstance) {


		$scope.transferencia = transferenciaData.data.transferencia[0];
    	$scope.title = "Documento Transferencia: " + $scope.transferencia.NUM_DOCUMENTO;
    	
    	$scope.transferencia.detalle = transferenciaData.data.detalle;
    	
        $scope.close = function () {
            $uibModalInstance.close();
        };

    }]);    