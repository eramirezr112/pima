angular.module('EjecucionPresupuestaria', ['ui.bootstrap'])
    .controller('EjecucionPresupuestariaController', ['$scope', '$uibModal', 'periodos', 'programas', 'cuentas', 'presupuesto', 'PresupuestoService', '$location', 'usuario', 'UsuarioService', 'PeriodoService', 'SolicitudService', 'OrdenPagoService', 'OrdenPagoDirectaService', 'EgresoService', 'TransferenciaService', function($scope, $uibModal, periodos, programas, cuentas, presupuesto, PresupuestoService, $location, usuario, UsuarioService, PeriodoService, SolicitudService, OrdenPagoService, OrdenPagoDirectaService, EgresoService, TransferenciaService) {

        //$scope.access = usuario.data.usuario.ind_presup;
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

        $scope.access = $scope.checkPermision(2);
        $scope.title = "Ejecución Presupuestaria";

        // Encabezado Data
        $scope.periodos          = periodos.data.periodos;
        $scope.programas         = programas.data.programas;
        $scope.cuentas           = cuentas.data.cuentas;
        $scope.presupuesto       = presupuesto.data.encabezado;
        $scope.totalesEncabezado = presupuesto.data.totales;
        $scope.registrosEncabeza = presupuesto.data.registros;
        $scope.activeRow         = -1;
        $scope.activeRowTab      = -1;

        // Get detalle for the first line
        var initInfo = {
            detalle: presupuesto.data.initDetalle
        };

        // Tabs
        $scope.reservado      = initInfo.detalle.reservado;
        $scope.aprobado       = initInfo.detalle.aprobado;
        $scope.ejecutado      = initInfo.detalle.ejecutado;
        $scope.modificaciones = initInfo.detalle.modificaciones;

        var initPrograma = null;
        initPrograma = {
            COD_PROGRAMA: 0,
            DES_PROGRAMA: "- TODOS -"
        };

        var initCuenta = null;
        initCuenta = {
            cod_cuenta: 0,
            des_cuenta: "- TODOS -"
        };

        $scope.programa = initPrograma;
        $scope.cuenta = initCuenta;
        $scope.cuentas.push(initCuenta);   

        $scope.setActive = function (index, codPrograma, codCuenta) {
            $scope.activeRow = index;
            
            PresupuestoService.getDetalle(codPrograma, codCuenta).then(function (result) {                
                $scope.reservado = result.data.detalle.reservado;
                $scope.tabs[0].content = result.data.detalle.reservado;
                $scope.tabs[1].content = result.data.detalle.aprobado;
                $scope.tabs[2].content = result.data.detalle.ejecutado;
                $scope.tabs[3].content = result.data.detalle.modificaciones;                
            });
                        
        };

        $scope.isActive = (index) => {
            return $scope.activeRow === index; 
        }

        $scope.isActive = (index) => {
            return $scope.activeRow === index; 
        } 

        $scope.isActiveRowTab = (index) => {
            return $scope.activeRowTab === index; 
        } 

        $scope.getDetalleFirst = function () {

            $scope.activeRow = -1;

            $scope.tabs[0].content = null;
            $scope.tabs[1].content = null;
            $scope.tabs[2].content = null;
            $scope.tabs[3].content = null;
            /*            
            setTimeout(function() {

                var codCuenta = $scope.filteredItems[0].cod_cuenta;
                var codPrograma = $scope.filteredItems[0].cod_programa;

                PresupuestoService.getDetalle(codPrograma, codCuenta).then(function (result) {                
                    $scope.reservado = result.data.detalle.reservado;
                    $scope.tabs[0].content = result.data.detalle.reservado;
                    $scope.tabs[1].content = result.data.detalle.aprobado;
                    $scope.tabs[2].content = result.data.detalle.ejecutado;
                    $scope.tabs[3].content = result.data.detalle.modificaciones;                
                }); 

            }, 1000);
            */            
        }               

        $scope.tabs = [
            { title:'Presup. Reservado', content:$scope.reservado     , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab1.html'},
            { title:'Presup. Aprobado' , content:$scope.aprobado      , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab2.html'},
            { title:'Presup. Ejecutado', content:$scope.ejecutado     , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab3.html'},
            { title:'Modificaciones'   , content:$scope.modificaciones, templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab4.html'},
        ];

        $scope.programFilter = function(tran) {            

            if ($scope.programa.DES_PROGRAMA == "- TODOS -" || !$scope.programa) {                
                if ($scope.filteredItems != undefined) {
                    var totales = {
                        tot_presupuesto_ordinario: 0,
                        tot_modificaciones: 0,
                        tot_total_presupuesto: 0,
                        tot_compromiso_reservado: 0,
                        tot_compromiso_aprobado: 0,
                        tot_ejecutado: 0,
                        tot_disponible: 0
                    };

                    var presupuesto_ordinario = 0;
                    angular.forEach($scope.filteredItems, function(line, key) {
                    
                      this.tot_presupuesto_ordinario += parseFloat(line.presupuesto_ordinario);
                      this.tot_modificaciones += parseFloat(line.modificaciones);
                      this.tot_total_presupuesto += parseFloat(line.total_presupuesto);
                      this.tot_compromiso_reservado += parseFloat(line.compromiso_reservado);
                      this.tot_compromiso_aprobado += parseFloat(line.compromiso_aprobado);
                      this.tot_ejecutado += parseFloat(line.ejecutado);
                      this.tot_disponible += parseFloat(line.disponible);
                    }, totales);

                    $scope.totalesEncabezado = totales;
                    $scope.registrosEncabeza = $scope.filteredItems.length;
                                        
                }
                return true;
            }
            else if($scope.programa.DES_PROGRAMA == tran.programa) {
                if(!$scope.filteredItems) {
                    if ($scope.filteredItems[0].programa == tran.programa) {                        
                        var totales = {
                            tot_presupuesto_ordinario: 0,
                            tot_modificaciones: 0,
                            tot_total_presupuesto: 0,
                            tot_compromiso_reservado: 0,
                            tot_compromiso_aprobado: 0,
                            tot_ejecutado: 0,
                            tot_disponible: 0
                        };

                        var presupuesto_ordinario = 0;
                        angular.forEach($scope.filteredItems, function(line, key) {
                          this.tot_presupuesto_ordinario += parseFloat(line.presupuesto_ordinario);
                          this.tot_modificaciones += parseFloat(line.modificaciones);
                          this.tot_total_presupuesto += parseFloat(line.total_presupuesto);
                          this.tot_compromiso_reservado += parseFloat(line.compromiso_reservado);
                          this.tot_compromiso_aprobado += parseFloat(line.compromiso_aprobado);
                          this.tot_ejecutado += parseFloat(line.ejecutado);
                          this.tot_disponible += parseFloat(line.disponible);
                        }, totales);

                        $scope.totalesEncabezado = totales;
                        $scope.registrosEncabeza = $scope.filteredItems.length;                   
                    }
                }
                
                return true;
            }
            else {
                return false;
            }
        }

        $scope.cuentaFilter = function(tran) { 
            
            if ($scope.cuenta.des_cuenta == "- TODOS -" || !$scope.cuenta) {                
                if ($scope.filteredItems != undefined) {
                    var totales = {
                        tot_presupuesto_ordinario: 0,
                        tot_modificaciones: 0,
                        tot_total_presupuesto: 0,
                        tot_compromiso_reservado: 0,
                        tot_compromiso_aprobado: 0,
                        tot_ejecutado: 0,
                        tot_disponible: 0
                    };

                    var presupuesto_ordinario = 0;
                    angular.forEach($scope.filteredItems, function(line, key) {
                    
                      this.tot_presupuesto_ordinario += parseFloat(line.presupuesto_ordinario);
                      this.tot_modificaciones += parseFloat(line.modificaciones);
                      this.tot_total_presupuesto += parseFloat(line.total_presupuesto);
                      this.tot_compromiso_reservado += parseFloat(line.compromiso_reservado);
                      this.tot_compromiso_aprobado += parseFloat(line.compromiso_aprobado);
                      this.tot_ejecutado += parseFloat(line.ejecutado);
                      this.tot_disponible += parseFloat(line.disponible);
                    }, totales);

                    $scope.totalesEncabezado = totales;
                    $scope.registrosEncabeza = $scope.filteredItems.length;
                                        
                }

                return true;
            }
            else if($scope.cuenta.des_cuenta == tran.descripcion){
                
                    console.log($scope.filteredItems.length);
                    
                    if ($scope.filteredItems.length > 0) {
                    
                        if ($scope.filteredItems[0].descripcion == tran.descripcion) {
                            
                            var totales = {
                                tot_presupuesto_ordinario: 0,
                                tot_modificaciones: 0,
                                tot_total_presupuesto: 0,
                                tot_compromiso_reservado: 0,
                                tot_compromiso_aprobado: 0,
                                tot_ejecutado: 0,
                                tot_disponible: 0
                            };

                            var presupuesto_ordinario = 0;
                            angular.forEach($scope.filteredItems, function(line, key) {
                              this.tot_presupuesto_ordinario += parseFloat(line.presupuesto_ordinario);
                              this.tot_modificaciones += parseFloat(line.modificaciones);
                              this.tot_total_presupuesto += parseFloat(line.total_presupuesto);
                              this.tot_compromiso_reservado += parseFloat(line.compromiso_reservado);
                              this.tot_compromiso_aprobado += parseFloat(line.compromiso_aprobado);
                              this.tot_ejecutado += parseFloat(line.ejecutado);
                              this.tot_disponible += parseFloat(line.disponible);
                            }, totales);

                            $scope.totalesEncabezado = totales;
                            $scope.registrosEncabeza = $scope.filteredItems.length;
                        }

                    }

                return true;
            } else {
                return false;
            }
        }

        $scope.viewDocument = function (index, codDocument, type) {            

            var baseTemplatePath = '../ng-app/views/ejecucion-presupuestaria/document/';
            var modalData = getModalData(type);
            $scope.opts = {
                backdrop: true,
                backdropClick: true,
                dialogFade: false,
                keyboard: true,
                templateUrl : baseTemplatePath + modalData.template,
                controller : modalData.controller,
                windowClass: 'app-modal-window',
                resolve: modalData.resolve
            };
        
            var modalInstance = $uibModal.open($scope.opts);

            modalInstance.result.then(function() {
                $scope.activeRowTab = index;
            });

            /**
             * Get document's data for the Modal Box
             */
            function getModalData(typeDoc) {

                var template = '';
                var controller = '';
                var resolve = {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    periodo: function (PeriodoService) {
                        return PeriodoService.get();
                    }
                };

                switch (type) {
                    // Documento Solicitud
                    case 1:
                        template = "solicitud.html";
                        controller = "DocumentoSolicitudController";
                        resolve.solicitudData = function (SolicitudService, $route) {
                            return SolicitudService.get(codDocument);
                        };
                        break;
                    // Documento Orden Pago
                    case 2:
                        template = "orden-pago.html";
                        controller = "DocumentoOrdenPagoController";
                        resolve.ordenData = function (OrdenPagoService, $route) {
                            return OrdenPagoService.get(codDocument);
                        };
                        break;
                    // Documento Orden Pago Directa
                    case 3:
                        template = "orden-pago-directa.html";
                        controller = "DocumentoOrdenPagoDirectaController";
                        resolve.ordenData = function (OrdenPagoDirectaService, $route) {
                            return OrdenPagoDirectaService.get(codDocument);
                        }
                    break;
                    // Documento Transferencia
                    case 4:
                        template = "transferencia.html";
                        controller = "DocumentoTransferenciaController";
                        resolve.transferenciaData = function (TransferenciaService, $route) {
                            return TransferenciaService.get(codDocument);
                        }
                    break;
                    // Documento Egreso
                    case 5:
                        template = "egreso.html";
                        controller = "DocumentoEgresoController";
                        resolve.egresoData = function (EgresoService, $route) {
                            return EgresoService.get(codDocument);
                        }
                    break;
                }

                var data = {
                    template: template,
                    controller: controller,
                    resolve: resolve
                };

                return data;
            };

        }

    }]);