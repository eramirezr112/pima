angular.module("PresupuestoService", []).factory("PresupuestoService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'presupuesto';
    var path = apiBase + controllerName;

    return {
        getAllCentroCostos: function () {
            var action = 'getAllCentroCostos';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getEncabezado: function(year, codCentro, codSubpartida, desCuenta){
            var action = 'getEncabezado';
            var data = {
                year: year,
                codCentro: codCentro,
                codSubpartida: codSubpartida,
                desCuenta: desCuenta
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getDetalle: function (codPrograma, codCuenta) {
            var action = 'getDetalle';
            var data = {
                codPrograma: codPrograma,
                codCuenta: codCuenta
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        }
    };
});