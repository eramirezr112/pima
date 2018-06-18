angular.module("pnlsys", [
	'Routes', 
    'Home',
	'Solicitud',
	'SolicitudService',
    'AddSolicitud',
    'EditSolicitud',
	'ViewSolicitud',
	'ProgramaService',
	'ProveedorService',
	'PeriodoService',
	'UsuarioService',
    'CuentaService',
    'PresupuestoService',
    'EjecucionPresupuestaria',
    'DocumentoSolicitud',
    'DocumentoOrdenPago',
    'DocumentoOrdenPagoDirecta',
    'DocumentoTransferencia',
    'DocumentoEgreso',
    'OrdenPagoService',
    'OrdenPagoDirectaService',
    'EgresoService',
    'TransferenciaService',
    'ConsultaVehicular',
    'ProvinciaService',
    'CantonService',
    'ConsultaVehicularService',
    'ViewConsultaVehicular',
    'MainMenu',
    'MainMenuService',
    'HeaderMenu',
    'HeaderMenuService',
    'Roles',
    'AddRol',
    'EditRol',
    'SistemaService',
    'Usuarios',
    'grid',
    'Vacaciones',
    'VacacionesService'
])
.filter('getEstado', function () {
    return function (input) {
        var output = "Confección";
        if (input === 2) {
            output = "Enviada";
        } else if (input === 3) {
            output = "Autorizada";
        } else if (input === 4) {
            output = "Rechazada";
        } else if (input === 5) {
            output = "Aprobacion DAF";
        } else if (input === 6) {
            output = "Aprobada";
        }

        return output;
    }
})
.filter('getEstadoOP', function () {

    return function (input) {
        var output = "Confección";
        if (input === 2) {
            output = "Aprob. Admin";
        } else if (input === 3) {
            output = "V.B. Auditoria";
        } else if (input === 4) {
            output = "Verif. Contable";
        }

        return output;
    }
})
.filter('getEstadoEgreso', function () {

    return function (input) {
        var output = "Aprobada";
        return output;
    }
})
.filter('getEstadoTran', function () {

    return function (input) {
        var output = "Aplicada";
        return output;
    }
})
.filter('getEstadoConsultaV', function () {

    return function (input) {
        var output = "Registrada";
        return output;
    }
})
.filter('getTipPlanilla', function () {

    return function (input) {
        var output = "Pago de Proveedores";
        if (input == 'S') {
            output = "Pago de Salarios";
        }        
        return output;
    }
})
.filter('getMoneda', function () {

    return function (input) {
        var output = "Colones";
        if (input === 2) {
            output = "Dolares";
        }

        return output;
    }
})
.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
})
.filter('customDate', function ($filter) {
    return function (input) {
        return $filter('date')(input, 'dd-MM-yyyy hh:mm');
    }
})
.filter('customDate2', function ($filter) {
    return function (input) {
        return $filter('date')(input, 'dd/MM/yyyy hh:mm');
    }
})
.filter('capitalizeEveryWord', function() {
      return function(input){        
        var text = input.toString();
        if (text != null) {


            if(text.indexOf(' ') !== -1){
              var inputPieces,
                  i;

              text = text.toLowerCase();
              inputPieces = text.split(' ');

              for(i = 0; i < inputPieces.length; i++){
                inputPieces[i] = capitalizeString(inputPieces[i]);
              }

              return inputPieces.toString().replace(/,/g, ' ');
            }
            else {
              text = text.toLowerCase();
              return capitalizeString(text);
            }
        }
        function capitalizeString(inputString){
          return inputString.substring(0,1).toUpperCase() + inputString.substring(1);
        }
      };
    });