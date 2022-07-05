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
        getYears: function () {
            var action = 'getYears';
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
            //console.log(data);
            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
            
        },
        getDetalle: function (nYear, nCodCentro, nCodSubpartida, nCodMeta) {
            var action = 'getDetalle';
            var data = {
                year          : nYear,
                codCentro     : nCodCentro,
                codSubpartida : nCodSubpartida,
                codMeta       : nCodMeta
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        }
    };
});