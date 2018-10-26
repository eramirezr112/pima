angular.module("SecurityRoutes", ['ngRoute'])
    .run(function($rootScope, $templateCache) {
        $rootScope.$on('$routeChangeStart', function(event, next, current) {
            if (typeof(current) !== 'undefined'){
                $templateCache.remove(current.templateUrl);
            }
        });
    })
    .config(['$routeProvider', function($routeProvider){

        $routeProvider
            .when('/', {
                templateUrl: 'ng-app/security/login/views/login.html',
                controller: 'LoginController'
            });

    }]);