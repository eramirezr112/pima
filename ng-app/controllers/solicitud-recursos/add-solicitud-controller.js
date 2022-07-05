angular.module('AddSolicitud', [])
    .controller('AddSolicitudController', ['$scope', '$filter', '$location', 'programas', 'proveedores', 'periodo', 'usuario', 'cuentas', 'lastSolicitud', 'tipoCambio', 'CuentaService', 'SolicitudService', function($scope, $filter, $location, programas, proveedores, periodo, usuario, cuentas, lastSolicitud, tipoCambio, CuentaService, SolicitudService) {
        
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

        $scope.title = "Nueva Solicitud";        
	    $scope.class = "selected-row";
	    $scope.activeRow = 0;
        $scope.disponible = 0;
        $scope.tipoCambio = tipoCambio.data.tipoCambio;
        $scope.warningTitle = "";
        $scope.warningLines = [];         
        $scope.warningStyle = {
            "background" : "#f5f5f5",
            "margin-bottom" : "10px"
        }

		$scope.setActive = function (index) {
			$scope.activeRow = index;
        	var item = $scope.solicitud.detalle[index];               
        	//$scope.disponible = item.mon_disponible_line;
            $scope.nDisponible = $scope.activeRow;            
		};

		$scope.isActive = (index) => {
        	var item = $scope.solicitud.detalle[index];
        	//$scope.disponible = item.mon_disponible_line;
			return $scope.activeRow === index; 
		}

        var initProveedor = {
        	cod_proveedor: 0,
        	des_proveedor: "- SELECCIONE UN PROVEEDOR -"
        };

        var initCuenta = {
        	cod_cuenta: 0,
        	des_cuenta: "- SELECCIONE UNA CUENTA -"
        };

        var listProgramas = programas.data.programas;
        var initPrograma = null;
        if ($scope.checkPermision(7)) {    
            initPrograma = {
                COD_PROGRAMA: 0,
                DES_PROGRAMA: "- SELECCIONE UN PROGRAMA -"
            };
            listProgramas.push(initPrograma);            
        } else {            
            initPrograma = {
                COD_PROGRAMA: programas.data.programas[0].COD_PROGRAMA,
                DES_PROGRAMA: programas.data.programas[0].DES_PROGRAMA
            };
        }

        var listProveedores = proveedores.data.proveedores;
        listProveedores.push(initProveedor);


        var listMonedas = [
        	{id: 1, des_moneda: 'Colones'},
        	{id: 2, des_moneda: 'Dolares'}
        ];        

        var listCuentas = cuentas.data.cuentas;
        listCuentas.push(initCuenta);

        $scope.detalle = [];
        $scope.solicitud = {
        	numSolicitud: lastSolicitud.data.maxNum[0].cod_solicitud+1,
        	periodo: periodo.data.periodo[0],
        	programa: initPrograma,
        	programas: listProgramas,
        	usuario: usuario.data.usuario.des_usuario,
        	fecha: $filter('date')(new Date(), 'dd-MM-yyyy hh:mm'),
        	estado: $filter('getEstado')('1'),
        	proveedor: initProveedor,
        	proveedores: listProveedores,
        	moneda: {id: 1, des_moneda: "Colones"},
        	monedas: listMonedas,
        	observaciones: '',
        	cuenta: initCuenta,
        	cuentas: listCuentas,
        	detalle: $scope.detalle,
            total: 0,
            tipoCambio: $scope.tipoCambio
        }

        $scope.addCuenta = function (kCuenta) { 

            //console.log(kCuenta);
            //console.log($scope.solicitud.detalle.length);
            //$scope.$watch("solicitud.cuentas", function(_newObj, _oldObj) {                

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
                //console.log($scope.solicitud.cuenta);
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

            //});            

            /*
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
        	//console.log($scope.solicitud.cuenta);
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
            */

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
                total += parseFloat($scope.solicitud.detalle[i].totLineh);
            }
            $scope.solicitud.total = total;
        }

        $scope.loadCuentas = function () {
       		var codPrograma = $scope.solicitud.programa.COD_PROGRAMA;
       		$scope.solicitud.detalle = [];

       		CuentaService.get(codPrograma).then(function (result) {
       			//$scope.solicitud.cuenta = initCuenta;
       			$scope.solicitud.cuentas = result.data.cuentas;
                //console.log($scope.solicitud.cuentas);
       		});
       		 	
        };

        $scope.saveSolicitud = function () {

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
                cod_usuario: usuario.data.usuario.cod_usuario, 
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
                    
                    SolicitudService.add(data).then(function (result) {
                       $location.path('../');
                    });                    

                } else {
                   alert("Favor verique la información provista en todas las lineas de detalle"); 
                }
                
            }

        }


    }])

    .directive('format', ['$filter', '$window', function ($filter, $window) {
        return {
            require: '?ngModel',
            scope: {
              moneda: '='
            },
            link: function (scope, elem, attrs, ctrl) {
                if (!ctrl) return;

                elem.on('focusin', function () {                    
                    
                    var info = this.value.split("");
                    var newValue = "";
                    for (var i = 0; i < info.length-2; i++) {
                        if (info[i] != "¢" && info[i] != "$" && info[i] != "," && info[i] != ".") {
                            newValue += info[i];
                        }
                    }

                    elem.val(newValue);
                    this.setSelectionRange(0, newValue.length);                    
                    
                });

                elem.on('click', function () {
                    if (!$window.getSelection().toString()) {
                        var info = this.value.split("");
                        var newValue = "";
                        for (var i = 0; i < info.length-2; i++) {
                            if (info[i] != "¢" && info[i] != "$" && info[i] != "," && info[i] != ".") {
                                newValue += info[i];
                            }
                        }

                        elem.val(newValue);
                        this.setSelectionRange(0, newValue.length);                        
                    }
                });

                ctrl.$formatters.unshift(function (a) {
                    var symbol = "¢";
                    if (scope.moneda == 2) {
                        symbol = "$";
                    }
                    return $filter(attrs.format)(ctrl.$modelValue, symbol);
                    //return ctrl.$modelValue;
                });

                elem.bind('blur', function(event) {
                    var plainNumber = elem.val().replace(/[^\d|\-+|\.+]/g, '');

                    var symbol = "¢";
                    if (scope.moneda == 2) {
                        symbol = "$";
                    }
                    elem.val($filter(attrs.format)(plainNumber, symbol));
                });
            }
        };
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