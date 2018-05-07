angular.module("TransferenciaService", []).factory("TransferenciaService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'transferencia';
    var path = apiBase + controllerName;

    return {
        get: function (numPlanilla) {
            var action = 'get';
            var data = {
                numPlanilla:numPlanilla
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});