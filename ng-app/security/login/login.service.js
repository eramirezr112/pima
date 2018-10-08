angular.module("LoginService", []).factory("LoginService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'login';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        validate: function (userData) {
            var action = 'validate';
            var data = {
                nickname: userData.nickname,
                password: userData.password
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        }
    };
});