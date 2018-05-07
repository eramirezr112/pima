angular.module("ConsultaVehicularService", []).factory("ConsultaVehicularService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'consultavehicular';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        get: function (idConsulta) {
            var action = 'get';
            var data = {
                idConsulta:idConsulta
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});