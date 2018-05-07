angular.module("MainMenuService", []).factory("MainMenuService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'mainmenu';
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