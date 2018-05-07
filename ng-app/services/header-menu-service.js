angular.module("HeaderMenuService", []).factory("HeaderMenuService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'headermenu';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getOptionsByUser: function(){
            var action = 'getOptionsByUser';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config); 
        }
    };
});