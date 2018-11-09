angular.module('ViewLiquidacionViaticos', ['ngMaterial'])
    .controller('ViewLiquidacionViaticosController', ['$scope', '$location', '$mdDialog', 'viaticoData', 'ViaticosService', function($scope, $location, $mdDialog, viaticoData, ViaticosService) {
       
        $scope.title = "Liquidación de Viáticos Nacionales";
        
        $scope.encabezado = viaticoData.data.encabezado[0];
        $scope.detalle    = viaticoData.data.detalle;
        
        $scope.totales     = viaticoData.data.totales;
        $scope.totAlmuerzo = $scope.totales.totMonAlmuerzo;
        $scope.totCena     = $scope.totales.totMonCena;
        $scope.totDesayuno = $scope.totales.totMonDesayuno;
        $scope.totEstadia  = $scope.totales.totMonEstadia;        

        $scope.aprobarSolicitud = function(id) {
            
            var confirm = $mdDialog.confirm({

                onComplete: function afterShowAnimation() {
                    var $dialog = angular.element(document.querySelector('md-dialog'));
                    var $actionsSection = $dialog.find('md-dialog-actions');
                    var $cancelButton = $actionsSection.children()[0];
                    var $confirmButton = $actionsSection.children()[1];
                    angular.element($confirmButton).addClass('btn-accept md-raised');
                    angular.element($cancelButton).addClass('btn-cancel md-raised');
                }

            })
            .title('¿Realmente desea aprobar esta Solicitud?')
            .ariaLabel('Lucky day')
            .targetEvent(id)
            .ok('Si')
            .cancel('No');

            $mdDialog.show(confirm).then(function() {

                var codSolicitud = $scope.encabezado.num_comprobante;
                var codCentro    = $scope.encabezado.cod_centro_costo;
                var codMeta      = $scope.encabezado.cod_meta;
                var monto        = $scope.encabezado.mon_comprobante;

                /*
                ViaticosService.approveSolicitud(codSolicitud, codCentro, codMeta, monto).then(function (result) {
                   
                    var response = result.data.response;

                    if (response == 1) {

                        var confirmResult = $mdDialog.confirm({
                                onComplete: function afterShowAnimation() {
                                    var $dialog = angular.element(document.querySelector('md-dialog'));
                                    var $actionsSection = $dialog.find('md-dialog-actions');
                                    var $cancelButton = $actionsSection.children()[0];
                                    var $confirmButton = $actionsSection.children()[1];
                                    angular.element($confirmButton).addClass('btn-accept md-raised');
                                }
                            })
                            .title('La solicitud fue aprobada satisfactoriamente')
                            .ariaLabel('Lucky day')
                            .targetEvent(id)
                            .ok('Aceptar');

                        $mdDialog.show(confirmResult).then(function() {
                            
                            $location.path('/liquidacion-viaticos-viaticos');

                        });

                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }

                });            
                */

            }, function() {
                
            });

        };        

        $scope.backToList = function () {            
            $location.path('/liquidacion-viaticos');
        };        

    }]);