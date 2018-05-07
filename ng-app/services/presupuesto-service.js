angular.module("PresupuestoService", []).factory("PresupuestoService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'presupuesto';
    var path = apiBase + controllerName;

    return {
        getEncabezado: function(programa, partida, estado){
            var action = 'getEncabezado';
            var data = {
                codPrograma: programa,
                codPartida: partida,
                codEstado: estado
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