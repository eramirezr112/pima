angular.module('ViewSolicitud', ['ngMaterial'])
    .controller('ViewSolicitudController', ['$scope', '$filter', '$mdDialog', '$location', 'usuario', 'solicitudData', 'SolicitudService', function($scope, $filter, $mdDialog, $location, usuario, solicitudData, SolicitudService) {
       
        $scope.title = "Solicitud de uso de Vehículos Oficiales";

        $scope.solicitud    = solicitudData.data.solicitud[0];
        $scope.funcionarios = solicitudData.data.funcionarios;

        $scope.aprobarSolicitud = function (id) {
            
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

                var codSolicitud = id;

                SolicitudService.approveSolicitud(codSolicitud).then(function (result) {
                   
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
                            
                            $location.path('/solicitud');

                        });

                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }

                });

            }, function() {
                
            });

            /*
            var r = confirm("¿Esta seguro que desea Aprobar esta solicitud?");
            if (r == true) {
                var codSolicitud = $scope.solicitud.cod_solicitud;

                SolicitudService.approveSolicitud(codSolicitud).then(function (result) {
                   
                    var response = result.data.response;

                    if (response == 1) {
                        alert('La solicitud ha sido aprobada');
                        $location.path('solicitud');
                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }

                });
            } else {
                return false;
            }
            */

        };

    }])
    .filter('toDate', function ($filter) {
        return function (input) {

            var formats = [
                moment.ISO_8601,
                "DD/MM/YYYY"
            ];

            var result = moment(input, formats, true).isValid();

            if (result) {
                return $filter('date')(input, 'dd/MM/yyyy');
            } else {
                return "Ninguna";
            }
        }
    })
    .filter('statusCode', function ($filter) {

        return function (input) {

            if ( input != null && (input.toString()).length == 1 && !input.match(/^-{0,1}\d+$/)){

                var output = "";
                if (input === 'E') {
                    output = "Entregado";
                } else if (input === 'C') {
                    output = "Confección";
                } else if (input === 'N') {
                    output = "Anuladas";
                } else if (input === 'P') {
                    output = "Préstamo";
                } else if (input === 'A') {
                    output = "Aprobadas";
                }

                return output;

            } else {
                return input;
            }
        }
    });