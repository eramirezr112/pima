angular.module("ProvinciaService", []).factory("ProvinciaService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'provincia';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        get: function(){
            var action = 'get';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        }
    };
});