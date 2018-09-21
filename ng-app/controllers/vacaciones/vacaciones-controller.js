angular.module('Vacaciones', ['ui.bootstrap', 'angularUtils.directives.dirPagination', 'ngMaterial'])
    .controller('SolicitudVacacionesController', ['$scope', '$http', '$mdDialog', 'vacaciones', 'usuario', 'VacacionesService', '$location', function($scope, $http, $mdDialog, vacaciones, usuario, VacacionesService, $location) {

        $scope.locationPath = $location.path();
        $scope.actions  = {'add':false, 'edit':false, 'view':true, 'delete':false, 'authorize':true};

        //$scope.totalSolicitudes = solicitudes.data.solicitudes.length;
        $scope.vacaciones = vacaciones.data.vacaciones;
        $scope.columns    = vacaciones.data.columns;
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
                console.log("Aprueba Solicitud! " + id);

                VacacionesService.get(id).then(function (vacacionData) {

                    $scope.solicitud    = vacacionData.data.solicitud[0];
                    $scope.saldoActual  = vacacionData.data.saldoActual;
                    $scope.diasGastados = vacacionData.data.diasGastados; 

                    $scope.newSaldoPeriodo = [];
                    
                    console.log(vacacionData.data);                
                    
                    var isEnableToApprove = true;
                    // Se valida que el saldo actual por periodo cubra los dias solicitados (Dias Gastados)
                    for (var dG = 0; dG < $scope.diasGastados.length; dG++) {

                        // Periodo de los dias a gastar
                        var periodDG = $scope.diasGastados[dG].NUM_PERIODO;
                        // Dias a gastar
                        var numDiasGastados = parseFloat($scope.diasGastados[dG].NUM_DIAS);
                        
                        // Periodo al que se le rebajaran los dias
                        var saldoPeriodo = $scope.getSaldoByPeriodo(periodDG);
                        console.log("Saldo Periodo");
                        console.log("==============================");
                        console.log(saldoPeriodo);
                        // Saldo actual del periodo en revision
                        var numSaldoPeriodo = parseFloat(saldoPeriodo.NUM_SALDO_PERIODO);

                        if (!(numSaldoPeriodo >= numDiasGastados)) {
                            isEnableToApprove = false; 
                        } else {
                            $scope.addToNewSaldoPeriodo(saldoPeriodo, numDiasGastados);
                        }

                    }

                    if (isEnableToApprove) {
                        console.log("Habilitado para Aprobar");
                        console.log($scope.newSaldoPeriodo);
                        console.log($scope.solicitud.cod_funcionario);

                        var data = {
                            newSaldoPeriodo: [$scope.newSaldoPeriodo],
                            codFuncionario: $scope.solicitud.cod_funcionario,
                            numSolicitud: $scope.solicitud.num_solicitud
                        };

                        VacacionesService.approve(data).then(function (response) {
                            console.log(response);
                            if (response.data.status) {
                                console.log("La solicitud fue aprobada satisfactoriamente");

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
                                    
                                    VacacionesService.all().then(function (vacaciones) {
                                        $scope.vacaciones = vacaciones.data.vacaciones;
                                    });

                                });

                            } else {
                                console.log("HA OCURRIDO UN ERROR! No se ha completado la aprobación");
                            }
                        });

                    } else {
                        console.log("NO Habilitado!");
                    }

                });

            }, function() {
                console.log("CANCELA Solicitud XXX! " + id);
                //console.log(vacacionData.data);
            });

        };

        $scope.backToList = function () {            
            $location.path('/vacaciones');
        };

        $scope.getSaldoByPeriodo = function (numPeriodo) {
            var objSaldo = [];
            for (var sA = 0; sA < $scope.saldoActual.length; sA++) {
                if (numPeriodo === $scope.saldoActual[sA].NUM_PERIODO) {
                    objSaldo = $scope.saldoActual[sA];
                    break;
                }
            }
            return objSaldo;
        };

        $scope.addToNewSaldoPeriodo = function (saldoPeriodo, numDays) {
            saldoPeriodo.DAYS_REQUEST = numDays;            
            $scope.newSaldoPeriodo.push(saldoPeriodo);
        };

    }]);