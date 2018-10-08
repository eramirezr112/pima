angular.module("SecurityRoutes", ['ngRoute'])
    .config(['$routeProvider', function($routeProvider){

        $routeProvider
            .when('/', {
                templateUrl: 'ng-app/security/login/views/login.html',
                controller: 'LoginController'
            });

    }]);