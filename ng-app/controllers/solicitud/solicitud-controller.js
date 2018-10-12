angular.module('Solicitud', ['ui.bootstrap', 'angularUtils.directives.dirPagination', 'ngMaterial'])
    .controller('SolicitudController', ['$scope', '$http', '$mdDialog', 'solicitudes', 'usuario', 'SolicitudService', '$location', function($scope, $http, $mdDialog, solicitudes, usuario, SolicitudService, $location) {

        $scope.locationPath = $location.path();
        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true};

        $scope.solicitudes = solicitudes.data.solicitudes;
        $scope.columns     = solicitudes.data.columns;

        // Se preparan las columnas a mostrar
        $scope.preparedColumns = [];
        angular.forEach($scope.columns, function(value, key) {            
            var newColumn = {
                visible: true,
                text: value
            };
            $scope.preparedColumns.push(newColumn);
        });

        // Se oculta la columna de solicitud
        $scope.preparedColumns[0].visible = false;

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
                            
                            SolicitudService.all().then(function (solicitudes) {
                                $scope.solicitudes = solicitudes.data.solicitudes;
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

        //console.log($scope.solicitudes);

        //$rootScope.$on('$routeChangeSuccess', function(scope, current, pre) {          
        
        //});

        /*
        $scope.setEstado = function (codSolicitud, status) {
            var r = confirm("¿Esta seguro que desea Aprobar esta solicitud?");
            if (r == true) {
            	var newStatus = status+1;
            	SolicitudService.changeStatus(codSolicitud, newStatus).then(function (result) {
            		window.location.reload();
            	});
            } else {
                return false;
            }
        };

        $scope.verifySolicitud = function (codSolicitud) {

            var r = confirm("¿Esta seguro que desea Enviar esta solicitud?");
            if (r == true) {

                SolicitudService.verifySolicitud(codSolicitud).then(function (result) {
                    var status = result.data.status;
                    if (status == "OK") {

                        var codPeriodo  = result.data.codPeriodo;
                        var codPrograma = result.data.codPrograma;
                        var moneda      = result.data.moneda;

                        SolicitudService.startAfectacionPresupuestaria(codSolicitud, codPeriodo, codPrograma, moneda).then(function (result) {
                            
                            SolicitudService.changeStatus(codSolicitud, 2).then(function (g) {
                                window.location.reload();
                            });

                        })

                    } else {

                        var listLines = "";
                        for (var l = 0; l < result.data.lineErrors.length; l++) {
                            listLines += result.data.lineErrors[l] + ",";
                        }

                        var str = listLines;
                        str = str.substring(0, str.length - 1);

                        alert("ATENCION: La Solicitud de Pedido NO puede ser enviada debido a que la(s) linea(s) " + str + " del detalle superan el monto disponible.\n\nFavor proceda a realizar la corrección(es) respectiva(s) y vuelva a intentarlo.");
                    }
                });

            } else {
                return false;
            }            

        };

        $scope.rejectSolicitud = function (codSolicitud) {
            var r = confirm("¿Esta seguro que desea Rechazar esta solicitud?");
            if (r == true) {
                SolicitudService.rejectAfectacionPresupuestaria(codSolicitud).then(function (result) {
                    SolicitudService.changeStatus(codSolicitud, 4).then(function (g) {
                        window.location.reload();
                    });
                });
            } else {
                return false;
            }            
        }

        $scope.denegateSolicitud = function (codSolicitud) {
            var r = confirm("¿Esta seguro que desea Denegar esta solicitud?");

            if (r == true) {
                SolicitudService.validateFactura(codSolicitud).then(function(r){

                    var cantidad = r.data.cantidad[0].cantidad;

                    if (cantidad > 0){
                        alert("Esta solicitud de pedido no se puede denegar porque tiene "+cantidad+" factura(s) asociada(s)");
                    } else {
                        
                        SolicitudService.rejectAfectacionPresupuestaria(codSolicitud).then(function (result) {
                            SolicitudService.changeStatus(codSolicitud, 4).then(function (g) {
                                window.location.reload();
                            });
                        });

                    }
                });
                
            } else {
                return false;
            }

        };

        $scope.setCompromisoAprobado = function (codSolicitud) {
            SolicitudService.setCompromisoAprobado(codSolicitud).then(function (result) {
                SolicitudService.changeStatus(codSolicitud, 5).then(function (g) {
                    window.location.reload();
                });
            });          
        };

        $scope.isModuleEnabled = function(module) {

            if (usuario.data.usuario.modulos_acceso != null){

                var enabled = true;                
                if (usuario.data.usuario.modulos_acceso != 'ALL') {                    
                    var modulos_enabled = usuario.data.usuario.modulos_acceso.split(',');                    
                    for (osa in modulos_enabled) {                    
                        if (modulos_enabled[osa] == module) {
                            enabled = true;
                            break;
                        } else {
                            enabled = false;
                        }
                    }
                    return enabled;

                } else {
                    return enabled;
                }
            } else {
                return true;
            }

        }; 

        $scope.checkPermision = function(option) {
            if (usuario.data.usuario.opt_sin_acceso != null){
                var opciones_sin_acceso = usuario.data.usuario.opt_sin_acceso.split(',');                
                var found = true;
                for (osa in opciones_sin_acceso) {                    
                    if (opciones_sin_acceso[osa] == option) {
                        found = false;
                        break;
                    }
                }
                return found;
            } else {
                return true;
            }

        };  

        if ($scope.checkPermision(9)) {
            $scope.tabs = [
                { title:'Por Autorizar', content:$scope.solicitudes, templateUrl: '../ng-app/views/solicitud/tabs/tab1.html'},
                { title:'Mis Solicitudes', content:$scope.solicitudesG, templateUrl: '../ng-app/views/solicitud/tabs/tab2.html'}
            ];
        } else {
            $scope.tabs = [
                { title:'Mis Solicitudes', content:$scope.solicitudesG, templateUrl: '../ng-app/views/solicitud/tabs/tab2.html'}
            ];
        }
        */

    }]);