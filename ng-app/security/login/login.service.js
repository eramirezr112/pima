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
            var data = null; 

            if (userData.hasOwnProperty("cod")){
                data = {
                    nickname: userData.nickname,
                    password: userData.password,
                    cod: userData.cod
                };
            } else {
                data = {
                    nickname: userData.nickname,
                    password: userData.password
                };                
            }


            return $http({
                url: path+'&f='+action, 
                method: 'post',
                data: $.param(data),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            });             
        }
    };
});