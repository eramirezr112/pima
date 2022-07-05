angular.module('ViewSolicitudRecursosAJ', ['ngMaterial'])
    .controller('ViewSolicitudRecursosAJController', ['$scope', '$filter', '$mdDialog', '$location', 'usuario', 'solicitudData', 'SolicitudService', function($scope, $filter, $mdDialog, $location, usuario, solicitudData, SolicitudService) {
       
        $scope.title = "Solicitudes de Recursos Jefatura";

        $scope.solicitud = solicitudData.data.solicitud[0];
        $scope.detalle   = solicitudData.data.detalle;

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
/*
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
                            
                            $location.path('/solicitud-recursos-aj');

                        });

                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }

                });

            }, function() {
                
            });
*/
        };

        $scope.showConfirm = function(ev) {
            console.log('Start Aprobación Solicitud');
        };

        $scope.backToList = function () {            
            $location.path('/solicitud-recursos-aj');
        };

        $scope.productos    = $scope.detalle;
        $scope.objetivosPao = $scope.solicitud.des_observaciones;
        $scope.terminosRef  = [$scope.solicitud.des_objetivo, $scope.solicitud.des_caracteristicas, $scope.solicitud.des_caracteristicas2];

        $scope.tabs = [
            { title:'Productos'    , content:$scope.productos   , templateUrl: '../ng-app/views/solicitud-recursos/tabs/productos.html?v='+session},
            { title:'Objetivo PAO' , content:$scope.objetivosPao, templateUrl: '../ng-app/views/solicitud-recursos/tabs/objetivosPao.html?v='+session},
            { title:'Términos Ref.', content:$scope.terminosRef , templateUrl: '../ng-app/views/solicitud-recursos/tabs/terminosRef.html?v='+session},
        ];

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
    .filter('sRStatusCode', function ($filter) {

        return function (input) {

            if ( input != null && (input.toString()).length == 1 && !input.match(/^-{0,1}\d+$/)){

                var output = "";
                if (input === 'N') {
                    output = "Recursos";
                } else if (input === 'S') {
                    output = "Suministros";
                } else if (input === 'C') {
                    output = "Complementaria";
                }

                return output;

            } else {
                return input;
            }
        }
    })
    .filter('sRCategoryCode', function ($filter) {

        return function (input) {

            if ( input != null){

                var output = "";
                if (input === '1') {
                    output = "Emergencia";
                } else if (input === '2') {
                    output = "Normal";
                } else if (input === '3') {
                    output = "Licitación";
                }

                return output;

            } else {
                return input;
            }
        }
    });