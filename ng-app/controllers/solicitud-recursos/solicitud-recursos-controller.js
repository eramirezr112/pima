angular.module('SolicitudRecursos', ['ui.bootstrap', 'angularUtils.directives.dirPagination', 'ngMaterial', 'ngSanitize'])
    .controller('SolicitudRecursosController', ['$scope', '$http', '$mdDialog', 'solicitudes', 'usuario', 'SolicitudRecursosService', '$location', function($scope, $http, $mdDialog, solicitudes, usuario, SolicitudRecursosService, $location) {

        $scope.codEmpleado = usuario.data.usuario.COD_EMPLEADO.trim();

        $scope.locationPath = $location.path();
        const locationPath  = $scope.locationPath.split('-');
        const locationPage  = locationPath[2];

        $scope.accessPage = true;
        $scope.titlePage = "Solicitudes de Recursos Jefatura";
        if(locationPage == 'al'){
            $scope.titlePage = "Solicitudes de Recursos Legal";
            if ($scope.codEmpleado !== 'ABG' && $scope.codEmpleado !== 'ABG1'){
                $scope.accessPage = false;
            }
        } else if (locationPage == 'ag') {
            $scope.titlePage = "Solicitudes de Recursos Gerencia";
            if ($scope.codEmpleado !== 'GGP'){
                $scope.accessPage = false;
            }
        }

        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true, 'reject': true, 'denied': true};
        console.log(solicitudes.data);
        $scope.solicitudes = solicitudes.data.recursos;                
        $scope.columns     = solicitudes.data.columns;
        
        console.log($scope.solicitudes);

        // Se preparan las columnas a mostrar
        $scope.preparedColumns = [];
        angular.forEach($scope.columns, function(value, key) { 
            var isCurrency = false;
            var isFromSolRecursos = false;
            if (value === 'monto') {
                isCurrency = true;
            }
            if (value === 'tipo') {
                isFromSolRecursos = true;
            }             
            var newColumn = {
                visible: true,
                text: value,
                isCurrency: isCurrency,
                isFromSolRecursos: isFromSolRecursos
            };
            $scope.preparedColumns.push(newColumn);
        });

        // Se oculta la columna de solicitud
        $scope.preparedColumns[6].visible = false;
        $scope.preparedColumns[7].visible = false;
        
        // Rol de Usuario    
        $scope.rolUsuario = usuario.data.usuario.rol_web;

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

                var numSolicitud = id;

                console.log(numSolicitud);
                
                SolicitudRecursosService.approveSolicitud(numSolicitud, locationPage).then(function (result) {
                   
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
                            
                            let indEstado = 1;
                            if (locationPage === 'ag') {
                                indEstado = 2;
                            } else if (locationPage === 'al') {
                                indEstado = 12;
                            } 

                            SolicitudRecursosService.all(indEstado).then(function (solicitudes) {
                                $scope.solicitudes = solicitudes.data.recursos;
                            });

                        });

                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }
                    

                });
                

            }, function() {
                console.log("NO");
            });       

        };

        $scope.denied = function (id) { 
            
            var confirm = $mdDialog.prompt({
                    onComplete: function afterShowAnimation() {
                        var $dialog = angular.element(document.querySelector('md-dialog'));
                        var $actionsSection = $dialog.find('md-dialog-actions');
                        var $cancelButton = $actionsSection.children()[0];
                        var $confirmButton = $actionsSection.children()[1];
                        angular.element($confirmButton).addClass('btn-accept md-raised');
                        angular.element($cancelButton).addClass('btn-cancel md-raised');
                    }
            })
            .title('Denegar Solicitud')
            .textContent('Favor ingrese el motivo de Denegación de la Solicitud N°' + id)
            .ariaLabel('Lucky day')
            .targetEvent(id)
            .ok('Denegar')
            .cancel('Cerrar');


            $mdDialog.show(confirm).then(function(motivo) {

                var numSolicitud = id;

                if(motivo === "" || motivo === undefined) {
                    var errorResult = $mdDialog.confirm()
                        .title('ERROR: No se ha podido denegar la Solicitud N° ' + numSolicitud)
                        .textContent('Debe de ingresar un motivo para la denegación de la Solicitud')
                        .ariaLabel('Lucky day')
                        .targetEvent(id)
                        .ok('Aceptar');

                    $mdDialog.show(errorResult).then(function() {
                    });

                } else {

                    SolicitudRecursosService.deniedSolicitud(numSolicitud, locationPage, motivo).then(function (result) {
                   
                    var response = result.data.response;

                    console.log(response);

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
                            .title('La solicitud ha sido denegada')
                            .ariaLabel('Lucky day')
                            .targetEvent(id)
                            .ok('Aceptar');

                        $mdDialog.show(confirmResult).then(function() {
                            
                            let indEstado = 1;
                            if (locationPage === 'ag') {
                                indEstado = 2;
                            } else if (locationPage === 'al') {
                                indEstado = 12;
                            } 

                            SolicitudRecursosService.all(indEstado).then(function (solicitudes) {
                                $scope.solicitudes = solicitudes.data.recursos;
                            });

                        });                        

                    } else {
                        alert('La solicitud No logró ser denegada');
                    }
                    

                });

                }

            }, function() {
                console.log("NO");
            });   

        };

        $scope.devolver = function (id) { 
            
            var confirm = $mdDialog.prompt({
                    onComplete: function afterShowAnimation() {
                        var $dialog = angular.element(document.querySelector('md-dialog'));
                        var $actionsSection = $dialog.find('md-dialog-actions');
                        var $cancelButton = $actionsSection.children()[0];
                        var $confirmButton = $actionsSection.children()[1];
                        angular.element($confirmButton).addClass('btn-accept md-raised');
                        angular.element($cancelButton).addClass('btn-cancel md-raised');
                    }
            })
            .title('Devolver Solicitud')
            .textContent('Favor ingrese el motivo de Devolución de la Solicitud N°' + id)
            .ariaLabel('Lucky day')
            .targetEvent(id)
            .ok('Devolver')
            .cancel('Cerrar');


            $mdDialog.show(confirm).then(function(motivo) {

                var numSolicitud = id;

                if(motivo === "" || motivo === undefined) {
                    var errorResult = $mdDialog.confirm()
                        .title('ERROR: No se ha podido devolver la Solicitud N° ' + numSolicitud)
                        .textContent('Debe de ingresar un motivo para la devolver de la Solicitud')
                        .ariaLabel('Lucky day')
                        .targetEvent(id)
                        .ok('Aceptar');

                    $mdDialog.show(errorResult).then(function() {
                    });

                } else {

                    SolicitudRecursosService.devolverSolicitud(numSolicitud, locationPage, motivo).then(function (result) {
                   
                    var response = result.data.response;

                    console.log(response);

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
                            .title('La solicitud ha sido devuelta')
                            .ariaLabel('Lucky day')
                            .targetEvent(id)
                            .ok('Aceptar');

                        $mdDialog.show(confirmResult).then(function() {
                            
                            let indEstado = 1;
                            if (locationPage === 'ag') {
                                indEstado = 2;
                            } else if (locationPage === 'al') {
                                indEstado = 12;
                            } 

                            SolicitudRecursosService.all(indEstado).then(function (solicitudes) {
                                $scope.solicitudes = solicitudes.data.recursos;
                            });

                        });

                    } else {
                        alert('La solicitud No logró ser denegada');
                    }
                    

                });

                }

            }, function() {
                console.log("NO");
            });   

        };

    }]);
