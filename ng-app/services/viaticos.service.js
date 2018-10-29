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
        }
    };
});