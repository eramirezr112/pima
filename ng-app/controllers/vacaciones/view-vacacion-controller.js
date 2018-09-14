angular.module('ViewVacacion', ['ngMaterial'])
    .controller('ViewVacacionController', ['$scope', '$mdDialog', '$filter', '$location', 'usuario', 'vacacionData', 'VacacionesService', function($scope, $mdDialog, $filter, $location, usuario, vacacionData, VacacionesService) {
       
        $scope.title = "Solicitud de Vacaciones";

        $scope.solicitud    = vacacionData.data.solicitud[0];
        $scope.saldoActual  = vacacionData.data.saldoActual;
        $scope.diasGastados = vacacionData.data.diasGastados;        
        console.log($scope.solicitud.cod_funcionario);
        $scope.showConfirm = function(ev) {
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
                .targetEvent(ev)
                .ok('Si')
                .cancel('No');

            $mdDialog.show(confirm).then(function() {
                $scope.newSaldoPeriodo = [];
                console.log("Aprueba Solicitud!");
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
                        codFuncionario: $scope.solicitud.cod_funcionario
                    };

                    VacacionesService.approve(data).then(function (result) {
                        console.log(result);
                    });

                } else {
                    console.log("NO Habilitado!");
                }



            }, function() {
                console.log("CANCELA Solicitud!");
                console.log(vacacionData.data);
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