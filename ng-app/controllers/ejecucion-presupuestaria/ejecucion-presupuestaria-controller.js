angular.module('EjecucionPresupuestaria', ['ui.bootstrap'])    
    .controller('EjecucionPresupuestariaController', ['$scope', '_', '$uibModal', 'centroCostos', 'presupuesto', 'listYears', 'PresupuestoService'  /*, 'periodos', 'programas', 'cuentas', 'presupuesto', 'PresupuestoService'*/, '$location', 'usuario', /*'UsuarioService', 'PeriodoService', 'SolicitudService', 'OrdenPagoService', 'OrdenPagoDirectaService', 'EgresoService', 'TransferenciaService',*/ function($scope, _, $uibModal, centroCostos, presupuesto, listYears, PresupuestoService, /*periodos, programas, cuentas, presupuesto, PresupuestoService, */ $location, usuario, /*UsuarioService, PeriodoService, SolicitudService, OrdenPagoService, OrdenPagoDirectaService, EgresoService, TransferenciaService*/) {        

        $scope.title = "Consulta de Ejecución Presupuestaria";
        $scope.codSubPartida = '';
        $scope.desSubPartida = '';
        
        $scope.centroCostos      = centroCostos.data.list;
        $scope.centroCosto       = centroCostos.data.first;

        $scope.encabezado        = presupuesto.data.encabezado;
        $scope.registrosEncabeza = presupuesto.data.registros;
        $scope.totalesEncabezado = presupuesto.data.totales;
        $scope.activeRow         = 0;

        $scope.years = listYears.data.all;        
        $scope.years = _.orderBy($scope.years, ['cod_year'], ['desc']);
        $scope.year  = listYears.data.current;

        // Get detalle for the first line
        var initInfo = {
            detalle: presupuesto.data.initDetalle
        };

        // Tabs
        $scope.provisional    = initInfo.detalle.provisional;
        $scope.definitivo     = initInfo.detalle.definitivo;
        $scope.real           = initInfo.detalle.real;        
        $scope.modificaciones = initInfo.detalle.modificaciones;

        $scope.setActive = function (index, nYear, nCodCentro, nCodSubpartida, nCodMeta) {
            $scope.isLoadingDetails = true;
            $scope.activeRow = index;
            PresupuestoService.getDetalle(nYear, nCodCentro, nCodSubpartida, nCodMeta).then(function (result) {
                $scope.tabs[0].content = result.data.detalle.provisional;
                $scope.tabs[1].content = result.data.detalle.definitivo;
                $scope.tabs[2].content = result.data.detalle.real;
                $scope.tabs[3].content = result.data.detalle.modificaciones;
                $scope.isLoadingDetails = false;
            });
                        
        };

        $scope.isActive = (index) => {
            return $scope.activeRow === index; 
        };

        $scope.getInfoByCentroCosto = function () {
            
            var year          = $scope.year.cod_year;
            var codCentro     = $scope.centroCosto.COD_CENTRO;
            var codSubpartida = $scope.codSubPartida;
            var desSubPartida     = $scope.desSubPartida;

            PresupuestoService.getEncabezado(year, codCentro, codSubpartida, desSubPartida).then(function (presupuesto) {

                $scope.encabezado        = presupuesto.data.encabezado;
                $scope.registrosEncabeza = presupuesto.data.registros;
                $scope.totalesEncabezado = presupuesto.data.totales;
                $scope.activeRow         = 0;

                // Get detalle for the first line
                var initInfo = {
                    detalle: presupuesto.data.initDetalle
                };

                $scope.tabs[0].content = initInfo.detalle.provisional;
                $scope.tabs[1].content = initInfo.detalle.definitivo;
                $scope.tabs[2].content = initInfo.detalle.real;
                $scope.tabs[3].content = initInfo.detalle.modificaciones;

            });
        };


        $scope.cleanFilters = () => {
            $scope.isLoadingData = true;
            $scope.year        = listYears.data.current;
            $scope.centroCosto = centroCostos.data.first;
            $scope.codSubPartida = '';
            $scope.desSubPartida = '';
            
            PresupuestoService.getEncabezado($scope.year.cod_year, $scope.centroCosto.COD_CENTRO, $scope.codSubPartida, $scope.desSubPartida).then(function (presupuesto) {

                $scope.encabezado        = presupuesto.data.encabezado;
                $scope.registrosEncabeza = presupuesto.data.registros;
                $scope.totalesEncabezado = presupuesto.data.totales;
                $scope.activeRow         = 0;

                // Get detalle for the first line
                var initInfo = {
                    detalle: presupuesto.data.initDetalle
                };

                $scope.tabs[0].content = initInfo.detalle.provisional;
                $scope.tabs[1].content = initInfo.detalle.definitivo;
                $scope.tabs[2].content = initInfo.detalle.real;
                $scope.tabs[3].content = initInfo.detalle.modificaciones;

                $scope.isLoadingData = false;

            });
            
        }

        $scope.filterAction = () => {
            $scope.isLoadingData = true;
            var year          = $scope.year.cod_year;
            var codCentro     = $scope.centroCosto.COD_CENTRO;
            var codSubpartida = $scope.codSubPartida;
            var desSubPartida = $scope.desSubPartida;

            PresupuestoService.getEncabezado(year, codCentro, codSubpartida, desSubPartida).then(function (presupuesto) {                
                $scope.encabezado        = presupuesto.data.encabezado;
                $scope.registrosEncabeza = presupuesto.data.registros;
                $scope.totalesEncabezado = presupuesto.data.totales;
                $scope.activeRow         = 0;

                // Get detalle for the first line
                var initInfo = {
                    detalle: presupuesto.data.initDetalle
                };

                $scope.tabs[0].content = initInfo.detalle.provisional;
                $scope.tabs[1].content = initInfo.detalle.definitivo;
                $scope.tabs[2].content = initInfo.detalle.real;
                $scope.tabs[3].content = initInfo.detalle.modificaciones;

                $scope.isLoadingData = false;

            });
        }

        $scope.tabs = [
            { title:'Comp. Provisional', content:$scope.provisional, templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab1.html'},
            { title:'Comp. Definitivo' , content:$scope.definitivo , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab2.html'},
            { title:'Gasto. Real'      , content:$scope.real       , templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab3.html'},
            { title:'Modificaciones'   , content:$scope.modificaciones, templateUrl: '../ng-app/views/ejecucion-presupuestaria/tabs/tab4.html'}
        ];


    }]);
