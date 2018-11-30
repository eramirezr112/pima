angular.module('AdelantoViaticos', ['ngMaterial'])
    .controller('AdelantoViaticosController', ['$scope', '$location', 'adelantoViaticos', '$mdDialog', 'ViaticosService', function ($scope, $location, adelantoViaticos, $mdDialog, ViaticosService) {

        $scope.locationPath = $location.path();
        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true};

        $scope.adelantoViaticos = adelantoViaticos.data.adelantoViaticos;
        $scope.columns          = adelantoViaticos.data.columns;

        // Se preparan las columnas a mostrar
        $scope.preparedColumns = [];
        angular.forEach($scope.columns, function(value, key) {    
            var isCurrency = false;
            if (value === 'monto') {
                isCurrency = true;
            }
            var newColumn = {
                visible: true,
                text: value,
                isCurrency: isCurrency
            };
            $scope.preparedColumns.push(newColumn);
        });

        $scope.codEdit = null;
        $scope.approve = function (id) {

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

                ViaticosService.getNumAdelanto(id).then(function (viaticoData) {

                    var viaticoInfo = viaticoData.data.encabezado[0];

                    var codSolicitud = id;
                    var codCentro    = viaticoInfo.cod_centro_costo;
                    var codMeta      = viaticoInfo.cod_meta;
                    var monto        = viaticoInfo.mon_adelanto;
                    
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

                                ViaticosService.getAllAdelantoViaticos().then(function (adelantoViaticos) {
                                    $scope.adelantoViaticos = adelantoViaticos.data.adelantoViaticos;
                                });                                

                            });

                        } else {
                            alert('La solicitud No puede aprobarse en estos momentos');
                        }

                    });                    

                });

            }, function() {

            });

        };

    }]);