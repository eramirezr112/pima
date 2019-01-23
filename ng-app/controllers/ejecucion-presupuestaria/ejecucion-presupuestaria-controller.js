angular.module('EjecucionPresupuestaria', ['ui.bootstrap'])
    .controller('EjecucionPresupuestariaController', ['$scope', '$uibModal', 'centroCostos', 'presupuesto'  /*, 'periodos', 'programas', 'cuentas', 'presupuesto', 'PresupuestoService'*/, '$location', 'usuario', /*'UsuarioService', 'PeriodoService', 'SolicitudService', 'OrdenPagoService', 'OrdenPagoDirectaService', 'EgresoService', 'TransferenciaService',*/ function($scope, $uibModal, centroCostos, presupuesto, /*periodos, programas, cuentas, presupuesto, PresupuestoService, */ $location, usuario, /*UsuarioService, PeriodoService, SolicitudService, OrdenPagoService, OrdenPagoDirectaService, EgresoService, TransferenciaService*/) {

        $scope.title = "Consulta de Ejecución Presupuestaria";
        $scope.centroCostos = centroCostos.data.list;
        $scope.centroCosto = centroCostos.data.first;
        $scope.encabezado = presupuesto.data.encabezado;
        $scope.registrosEncabeza = presupuesto.data.registros;
        $scope.totalesEncabezado = presupuesto.data.totales;
        console.log(presupuesto.data);

        var minYear = 2004;
        var current_year = new Date().getFullYear();
        $scope.years = [];
        $scope.year = {'cod_year': current_year, 'num_year': current_year};

        for (var i = minYear; i <= current_year; i++) {
            var year_data = {'cod_year': i, 'num_year': i};
            $scope.years.push(year_data);
        }

        // Get detalle for the first line
        var initInfo = {
            detalle: presupuesto.data.initDetalle
        };

        // Tabs
        $scope.provisional    = initInfo.detalle.provisional;
        $scope.definitivo     = initInfo.detalle.definitivo;
        $scope.real           = initInfo.detalle.real;        
        $scope.modificaciones = initInfo.detalle.modificaciones;        

        /*
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
        $scope.provisional = initInfo.detalle.provisional;
        $scope.definitivo  = initInfo.detalle.definitivo;
        $scope.real        = initInfo.detalle.real;
        //$scope.modificaciones = initInfo.detalle.modificaciones;

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
        }               
        */

        $scope.tabs = [
            { title:'Comp. Provisional', content:$scope.provisional, templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab1.html'},
            { title:'Comp. Definitivo' , content:$scope.definitivo , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab2.html'},
            { title:'Gasto. Real'      , content:$scope.real       , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab3.html'},
            { title:'Modificaciones'   , content:$scope.modificaciones, templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab4.html'}
        ];

        /*
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
        */

    }]);