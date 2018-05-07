angular.module("SistemaService", []).factory("SistemaService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'sistema';
    var path = apiBase + controllerName;

    return {
        getRoles: function() {
            var action = 'getRoles';        
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getPermisos: function () {
            var action = 'getPermisos';
            var config = {
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        getInfoRol: function(idRol) {
            var action = 'getInfoRol';
            var data = {
                idRol: idRol
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config); 
        }
    };
});