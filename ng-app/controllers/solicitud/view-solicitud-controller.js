angular.module('ViewSolicitud', [])
    .controller('ViewSolicitudController', ['$scope', '$filter', '$location', 'usuario', 'solicitudData', 'SolicitudService', function($scope, $filter, $location, usuario, solicitudData, SolicitudService) {
       
        $scope.title = "Solicitud de uso de Vehiculos Oficiales";

        $scope.solicitud    = solicitudData.data.solicitud[0];
        $scope.funcionarios = solicitudData.data.funcionarios;

        console.log($scope.solicitud);
        console.log($scope.funcionarios);
        

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