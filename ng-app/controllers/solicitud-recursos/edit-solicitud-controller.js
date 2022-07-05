angular.module('EditSolicitud', [])
    .controller('EditSolicitudController', ['$scope', '$filter', '$location', 'programas', 'proveedores', 'periodo', 'cuentas', 'solicitudData', 'tipoCambio', 'CuentaService', 'SolicitudService', function($scope, $filter, $location, programas, proveedores, periodo, cuentas, solicitudData, tipoCambio, CuentaService, SolicitudService) {
        
        var solicitud = solicitudData.data.solicitud[0];        
        var detalle = solicitudData.data.detalle;
        $scope.title = "Editar Solicitud";
	    $scope.class = "selected-row";
	    $scope.activeRow = 0;
        $scope.tipoCambio = tipoCambio.data.tipoCambio;
        $scope.warningTitle = "";
        $scope.warningLines = [];         
        $scope.warningStyle = {
            "background" : "#f5f5f5",
            "margin-bottom" : "10px"
        }        

        $scope.user = solicitud.COD_USUARIO; 

		$scope.setActive = (index) => {
			$scope.activeRow = index;
        	var item = $scope.solicitud.detalle[index];
        	//$scope.disponible = item.mon_disponible;
            $scope.nDisponible = $scope.activeRow;
		};

		$scope.isActive = (index) => {
        	var item = $scope.solicitud.detalle[index];
        	//$scope.disponible = item.mon_disponible;
			return $scope.activeRow === index; 
		}

        var initProveedor = {
        	cod_proveedor: 0,
        	des_proveedor: "- SELECCIONE UN PROVEEDOR -"
        };

        // Se carga el proveedor
        var proveedor = null;
        if (solicitud.cod_proveedor != null) {
            proveedor = {
                cod_proveedor: solicitud.cod_proveedor, 
                des_proveedor: solicitud.des_proveedor
            };
        } else {
            proveedor = initProveedor;
        }

        var initCuenta = {
        	cod_cuenta: 0,
        	des_cuenta: "- SELECCIONE UNA CUENTA -"
        };

        var listProgramas = programas.data.programas;
        var programa = {
            COD_PROGRAMA: solicitud.COD_PROGRAMA,
            DES_PROGRAMA: solicitud.DES_PROGRAMA
        };

        var listProveedores = proveedores.data.proveedores;
        listProveedores.push(initProveedor);

        // Se carga la Moneda
        var descM = "Colones";
        if (solicitud.IND_MONEDA == 2) {
            descM = "Dolares";
        }
        var moneda = {id: solicitud.IND_MONEDA, des_moneda: descM};
        var listMonedas = [
        	{id: 1, des_moneda: 'Colones'},
        	{id: 2, des_moneda: 'Dolares'}
        ];        

        var listCuentas = cuentas.data.cuentas;
        listCuentas.push(initCuenta);

        var currencySymbol = "¢";
        if (moneda.id == 2) {
            currencySymbol = "$";
        }
        // Prepare all detail lines
        var lines = [];
        var wLines = [];
        for (var n = 0; n < detalle.length; n++) {
            
            if (parseFloat(detalle[n].totLine) > parseFloat(detalle[n].mon_disponible)) {
                var warningLine = "El Total de la linea de detalle #"+(n+1)+" supera el disponible permitido";
                $scope.warningTitle = "ADVERTENCIA:";
                wLines[n] = warningLine;
            }
            
            var line = {
                cantidad:detalle[n].cantidad,
                cod_cuenta:detalle[n].cod_cuenta,
                des_cuenta:detalle[n].des_cuenta,
                descripcion:detalle[n].descripcion,
                mon_disponible:parseFloat(detalle[n].mon_disponible),
                mon_disponible_line:parseFloat(detalle[n].mon_disponible),
                num_cuenta:detalle[n].num_cuenta,
                preUnit:parseFloat(detalle[n].preUnit),
                totLine:$filter('currency')(parseFloat(detalle[n].totLine), currencySymbol),
                totLineh:parseFloat(detalle[n].totLine)
            };

            lines.push(line);
        }

        if (wLines.length == 0){
            $scope.warningTitle = "";
            $scope.warningStyle = {
                "background" : "#f5f5f5",
                "margin-bottom" : "10px"
            }                
        } else {
            $scope.warningStyle = {
                "background" : "#ffff00",
                "margin-bottom" : "10px"
            }                
        }

        $scope.warningLines = wLines;

        $scope.detalle = lines;
        $scope.solicitud = {
        	numSolicitud: solicitud.COD_SOLICITUD,
        	periodo: periodo.data.periodo[0],
        	programa: programa,
        	programas: listProgramas,
        	usuario: solicitud.des_usuario,
        	fecha: $filter('date')(new Date(), 'dd-MM-yyyy hh:mm'),
        	estado: $filter('getEstado')('1'),
        	proveedor: proveedor,
        	proveedores: listProveedores,
        	moneda: moneda,
        	monedas: listMonedas,
        	observaciones: solicitud.DES_OBSERVACION,
        	cuenta: initCuenta,
            //cuentas: listCuentas,
        	cuentas:CuentaService.get(solicitud.COD_PROGRAMA).then(function (result) {
                
                $scope.solicitud.cuentas = result.data.cuentas;
                
            }),
        	detalle: $scope.detalle,
            total: parseFloat(solicitud.MON_TOTAL),
            tipoCambio: $scope.tipoCambio
        }
        //solicitud.COD_PROGRAMA;
        $scope.addCuenta = function (kCuenta) { 

            $scope.solicitud.cuenta = $scope.solicitud.cuentas[kCuenta];

            var detCuenta = {
                cod_cuenta: $scope.solicitud.cuenta.cod_cuenta,
                des_cuenta: $scope.solicitud.cuenta.des_cuenta,
                mon_disponible: $scope.solicitud.cuenta.mon_disponible,
                mon_disponible_line: $scope.solicitud.cuenta.mon_disponible,
                num_cuenta: $scope.solicitud.cuenta.num_cuenta,
                cantidad: 0,
                descripcion: '', 
                preUnit: 0,
                totLine: 0
            };

            var found = false;
            for (var i = 0; i < $scope.solicitud.detalle.length; i++) {
                if($scope.solicitud.detalle[i].cod_cuenta == detCuenta.cod_cuenta){
                    found = true;
                }
            }

            if (!found) {
                $scope.solicitud.detalle.push(detCuenta);
            } else {
                alert("Antención:\nEsta cuenta ya se encuentra en las lineas de detalle,\nModifique la cantidades segun la linea que desea agregar");
            }
            $scope.solicitud.cuenta = initCuenta;

        };

        $scope.getTotalByLine = function (numLine) {

            $scope.solicitud.detalle[numLine].cantidadh = $scope.solicitud.detalle[numLine].cantidad;
            $scope.solicitud.detalle[numLine].preUnith = $scope.solicitud.detalle[numLine].preUnit;
            var totByLine = $scope.solicitud.detalle[numLine].cantidad * $scope.solicitud.detalle[numLine].preUnit;
            var symbol = "¢";
            if ($scope.solicitud.moneda.id == 2) {
                symbol = "$";
            }

            $scope.solicitud.detalle[numLine].totLine = $filter('currency')(totByLine, symbol);
            $scope.solicitud.detalle[numLine].totLineh = $scope.solicitud.detalle[numLine].cantidadh * $scope.solicitud.detalle[numLine].preUnith;
            $scope.solicitud.detalle[numLine].mon_disponible_line = $scope.solicitud.detalle[numLine].mon_disponible;

            // ---- Magange the Warning messages ----
            var wLines = [];
            for (var w = 0; w < $scope.solicitud.detalle.length; w++) {
                if ($scope.solicitud.detalle[w].totLineh > $scope.solicitud.detalle[w].mon_disponible_line) {
                    var warningLine = "El Total de la linea de detalle #"+(w+1)+" supera el disponible permitido";
                    $scope.warningTitle = "ADVERTENCIA:";
                    wLines[w] = warningLine;
                }
            }
            
            if (wLines.length == 0){
                $scope.warningTitle = "";
                $scope.warningStyle = {
                    "background" : "#f5f5f5",
                    "margin-bottom" : "10px"
                }                
            } else {
                $scope.warningStyle = {
                    "background" : "#ffff00",
                    "margin-bottom" : "10px"
                }                
            }

            $scope.warningLines = wLines;
            // ---- END Warning messages ----

            var total = 0;
            for (var i = 0; i < $scope.solicitud.detalle.length; i++) {                
                total += $scope.solicitud.detalle[i].totLineh;
            }
            $scope.solicitud.total = total;
            
        }

        $scope.removeCuenta = function (item) {
			var index=$scope.solicitud.detalle.indexOf(item);
			$scope.solicitud.detalle.splice(index,1);

            // ---- Magange the Warning messages ----
            var wLines = [];
            for (var w = 0; w < $scope.solicitud.detalle.length; w++) {
                if ($scope.solicitud.detalle[w].totLineh > $scope.solicitud.detalle[w].mon_disponible_line) {
                    var warningLine = "El Total de la linea de detalle #"+(w+1)+" supera el disponible permitido";
                    $scope.warningTitle = "ADVERTENCIA:";
                    wLines[w] = warningLine;
                }
            }
            
            if (wLines.length == 0){
                $scope.warningTitle = "";
                $scope.warningStyle = {
                    "background" : "#f5f5f5",
                    "margin-bottom" : "10px"
                }                
            } else {
                $scope.warningStyle = {
                    "background" : "#ffff00",
                    "margin-bottom" : "10px"
                }                
            }

            $scope.warningLines = wLines;
            // ---- END Warning messages ----

            var total = 0;
            for (var i = 0; i < $scope.solicitud.detalle.length; i++) {
                total += $scope.solicitud.detalle[i].totLineh;
            }
            $scope.solicitud.total = total;
        }

        $scope.loadCuentas = function () {
       		var codPrograma = $scope.solicitud.programa.COD_PROGRAMA;
       		$scope.solicitud.detalle = [];

       		CuentaService.get(codPrograma).then(function (result) {       			
       			//$scope.solicitud.cuenta = initCuenta;
       			$scope.solicitud.cuentas = result.data.cuentas;
                console.log($scope.solicitud.cuentas);
       			//$scope.solicitud.cuentas.push(initCuenta);
       		});
       		 	
        };

        $scope.updateSolicitud = function () {

            if ($scope.solicitud.moneda.id == 2 && $scope.tipoCambio == 0) {
                alert("ATENCION!\n\nAl ser una solicitud bajo la moneda dólares, la solicitud No puede ser almacenada porque No existe tipo de cambio registrado a la fecha.\n\nFavor contactar a Tesorería");
                return false;
            }

            var errors = false;
            if ($scope.solicitud.programa['COD_PROGRAMA'] == 0) {
                errors = true;
                alert("ATENCION! Debe de Seleccionar un Programa");
                return false;
            }

            if ($scope.solicitud.detalle.length == 0) {
                errors = true;
                alert("ATENCION! Debe de existir al menos una linea de Detalle");
                return false;
            }

            //Encabezado
            var encabezado = {
                cod_solicitud: $scope.solicitud.numSolicitud, 
                cod_periodo: $scope.solicitud.periodo['cod_periodo'], 
                cod_programa: $scope.solicitud.programa['COD_PROGRAMA'], 
                cod_proveedor: $scope.solicitud.proveedor['cod_proveedor'], 
                fec_registro: $scope.solicitud.fecha, 
                cod_usuario: $scope.user, 
                des_observacion: $scope.solicitud.observaciones, 
                ind_estado: 1, 
                mon_total: $scope.solicitud.total, 
                ind_moneda: $scope.solicitud.moneda.id,
                tipo_cambio: $scope.tipoCambio
            };

            // Detalle
            var detalle = {
                data: $scope.solicitud.detalle
            };

            var data = {
                encabezado: encabezado,
                detalle: detalle
            };
            
            if (errors == false){
                var emptyLines = false;
                for (var i = 0; i < $scope.solicitud.detalle.length; i++) {
                    
                    if (parseInt($scope.solicitud.detalle[i].cantidad) == 0 || isNaN($scope.solicitud.detalle[i].cantidad) == true || $scope.solicitud.detalle[i].cantidad == "") {                        
                        emptyLines = true;
                    }

                    if (parseFloat($scope.solicitud.detalle[i].preUnit) == 0 || isNaN($scope.solicitud.detalle[i].preUnit) == true || $scope.solicitud.detalle[i].preUnit == "") {
                        emptyLines = true;
                    }

                    if ($scope.solicitud.detalle[i].descripcion.length == 0) {
                        emptyLines = true;
                    }
                }                

                if (emptyLines == false) {
                    SolicitudService.update(data).then(function (result) {                    
                       $location.path('../');
                    });
                } else {
                   alert("Favor verique la información provista en todas las lineas de detalle"); 
                }
                
            }


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