angular.module("ViaticosService", []).factory("ViaticosService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'viatico';
    var path = apiBase + controllerName;

    return {
        getAllAdelantoViaticos: function(){
            var action = 'getAllAdelantoViaticos';
            var config = {
                headers : {'Accept' : 'application/json'}
            };     
            return $http.get(path+'&f='+action, config);
        },
        getNumAdelanto: function (numAdelanto) {
            var action = 'getNumAdelanto';
            var data = {
                numAdelanto: numAdelanto
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        approveSolicitud: function (codSolicitud, codCentro, codMeta, monto) {
            var action = 'approveSolicitud';
            var data = {
                codSolicitud: codSolicitud,
                codCentro: codCentro,
                codMeta: codMeta,
                monto: monto
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getAllLiquidacionViaticos: function(){
            var action = 'getAllLiquidacionViaticos';
            var config = {
                headers : {'Accept' : 'application/json'}
            };     
            return $http.get(path+'&f='+action, config);
        },
        getNumComprobante: function (numComprobante) {
            var action = 'getNumComprobante';
            var data = {
                numComprobante: numComprobante
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        approveLiquidacionViaticos: function (codSolicitud, codCentro, codMeta, monto) {
            var action = 'approveLiquidacionViaticos';
            var data = {
                codSolicitud: codSolicitud,
                codCentro: codCentro,
                codMeta: codMeta,
                monto: monto
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        }
    };
});