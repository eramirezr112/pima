angular.module("ProveedorService", []).factory("ProveedorService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'proveedor';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        }
    };
});