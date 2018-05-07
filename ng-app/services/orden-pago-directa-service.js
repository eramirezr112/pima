angular.module("OrdenPagoDirectaService", []).factory("OrdenPagoDirectaService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'ordenpagodirecta';
    var path = apiBase + controllerName;

    return {
        get: function (idOrden) {
            var action = 'get';
            var data = {
                idOrden:idOrden
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});