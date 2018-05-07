angular.module("UsuarioService", []).factory("UsuarioService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'usuario';
    var path = apiBase + controllerName;

    return {
        get: function(){
            var action = 'get';        
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        all: function () {
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        getPermisionByUser: function (codUser) {
            var action = 'getPermisionByUser';
            var data = {
                codUser:codUser
            };            
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        savePermits: function (rolData) {
            var action = 'savePermits';
            var data = {
                rolData: rolData
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        updateRol: function (rolData) {
            var action = 'updateRol';
            var data = {
                rolData: rolData
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        updateUserRol: function (userData) {
            var action = 'updateUserRol';
            var data = {
                userData: userData
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        }, 
        deleteRol: function (rol) {
            var action = 'deleteRol';
            var data = {
                codRol: rol
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        }
    };
});