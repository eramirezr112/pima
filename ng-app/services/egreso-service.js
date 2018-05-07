angular.module("EgresoService", []).factory("EgresoService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'egreso';
    var path = apiBase + controllerName;

    return {
        get: function (idEgreso) {
            var action = 'get';
            var data = {
                idEgreso:idEgreso
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});