angular.module('ViewVacacion', [])
    .controller('ViewVacacionController', ['$scope', '$filter', '$location', 'usuario', 'vacacionData', 'VacacionesService', function($scope, $filter, $location, usuario, vacacionData, VacacionesService) {
       
        $scope.title = "Solicitud de Vacaciones";

        $scope.solicitud    = vacacionData.data.solicitud[0];
        $scope.saldoActual  = vacacionData.data.saldoActual;
        $scope.diasGastados = vacacionData.data.diasGastados;
        console.log(vacacionData.data);
        //$scope.funcionarios = solicitudData.data.funcionarios;

        /*
        $scope.aprobarSolicitud = function () {
            
            var r = confirm("¿Esta seguro que desea Aprobar esta solicitud?");
            if (r == true) {
                var codSolicitud = $scope.solicitud.cod_solicitud;

                SolicitudService.approveSolicitud(codSolicitud).then(function (result) {
                   
                    var response = result.data.response;

                    if (response == 1) {
                        alert('La solicitud ha sido aprobada');
                        $location.path('../');
                    } else {
                        alert('La solicitud No puede aprobarse en estos momentos');
                    }

                });
            } else {
                return false;
            }

        };
        */

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