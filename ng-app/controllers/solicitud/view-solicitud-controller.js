angular.module('ViewSolicitud', [])
    .controller('ViewSolicitudController', ['$scope', '$filter', 'periodo', 'solicitudData', function($scope, $filter, periodo, solicitudData) {
        
        var solicitud = solicitudData.data.solicitud[0];
        var detalle = solicitudData.data.detalle;        
        $scope.title = "Ver Solicitud: " + solicitud.COD_SOLICITUD;
	    $scope.class = "selected-row";
	    $scope.activeRow = 0;

		$scope.setActive = (index) => {
			$scope.activeRow = index;
        	var item = $scope.solicitud.detalle[index];
        	$scope.disponible = item.mon_disponible;
		};

		$scope.isActive = (index) => {
        	var item = $scope.solicitud.detalle[index];
        	$scope.disponible = item.mon_disponible;
			return $scope.activeRow === index; 
		}

        var initProveedor = {
        	cod_proveedor: 0,
        	des_proveedor: ""
        };

        var programa = {
            COD_PROGRAMA: solicitud.COD_PROGRAMA,
            DES_PROGRAMA: solicitud.DES_PROGRAMA
        };
        
        // Se carga el proveedor
        var proveedor = null;
        if (solicitud.cod_proveedor != "") {
            proveedor = {
                cod_proveedor: solicitud.cod_proveedor, 
                des_proveedor: solicitud.des_proveedor
            };
        } else {
            proveedor = initProveedor;
        }

        // Se carga la Moneda
        var descM = "Colones";
        if (solicitud.IND_MONEDA == 2) {
            descM = "Dolares";
        }
        var moneda = {id: solicitud.IND_MONEDA, des_moneda: descM};
        
        // Prepare all detail lines
        var lines = [];
        for (var n = 0; n < detalle.length; n++) {            

            var line = {
                cantidad:detalle[n].cantidad,
                cod_cuenta:detalle[n].cod_cuenta,
                des_cuenta:detalle[n].des_cuenta,
                descripcion:detalle[n].descripcion,
                mon_disponible:parseFloat(detalle[n].mon_disponible),
                num_cuenta:detalle[n].num_cuenta,
                preUnit:parseFloat(detalle[n].preUnit),
                totLine:parseFloat(detalle[n].totLine)
            };

            lines.push(line);
        }

        $scope.detalle = lines;
        $scope.solicitud = {
        	numSolicitud: solicitud.COD_SOLICITUD,
        	periodo: periodo.data.periodo[0],
        	programa: programa,
        	usuario: solicitud.des_usuario,
        	fecha: $filter('date')(solicitud.FEC_REGISTRO, 'dd-MM-yyyy hh:mm'),
        	estado: $filter('getEstado')(solicitud.IND_ESTADO),
        	proveedor: proveedor,
        	moneda: moneda,
        	observaciones: solicitud.DES_OBSERVACION,
        	detalle: $scope.detalle,
            total: parseFloat(solicitud.MON_TOTAL)
        }


    }])

	.filter('cut', function () {
        return function (value, wordwise, max, tail) {
            if (!value) return '';

            max = parseInt(max, 10);
            if (!max) return value;
            if (value.length <= max) return value;

            value = value.substr(0, max);
            if (wordwise) {
                var lastspace = value.lastIndexOf(' ');
                if (lastspace !== -1) {
                  //Also remove . and , so its gives a cleaner result.
                  if (value.charAt(lastspace-1) === '.' || value.charAt(lastspace-1) === ',') {
                    lastspace = lastspace - 1;
                  }
                  value = value.substr(0, lastspace);
                }
            }

            return value + (tail || ' …');
        };
    });