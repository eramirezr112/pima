angular.module("CuentaService", []).factory("CuentaService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'cuenta';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        get: function (codPrograma) {
            var action = 'get';
            var data = {
                codPrograma:codPrograma
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});