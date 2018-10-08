angular.module('Login', ['ngMaterial'])

    .controller('LoginController', ['$scope', '$mdDialog', '$window', 'LoginService', function ($scope, $mdDialog, $window, LoginService) {
    
        $scope.titleButton = "INGRESAR";

        $scope.validate = function () {

            var validateData = {
                nickname: $scope.user,
                password: $scope.pass
            };

            LoginService.validate(validateData).then(function (result) {
                var status = result.data.response;
                
                if (status) {                    
                    $window.location.href = "../app";
                } else {

                    var id = 17;

                    var confirmResult = $mdDialog.confirm({
                            onComplete: function afterShowAnimation() {
                                var $dialog = angular.element(document.querySelector('md-dialog'));
                                var $actionsSection = $dialog.find('md-dialog-actions');
                                var $cancelButton = $actionsSection.children()[0];
                                var $confirmButton = $actionsSection.children()[1];
                                angular.element($confirmButton).addClass('btn-accept md-raised');
                            }
                        })
                        .title('Los credenciales de acceso son inválidos')
                        .ariaLabel('Lucky day')
                        .targetEvent(id)
                        .ok('Aceptar');

                    $mdDialog.show(confirmResult).then(function() {

                    });
                   
                }

            });

        };

    }])