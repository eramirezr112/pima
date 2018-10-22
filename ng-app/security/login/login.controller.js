angular.module('Login', ['ngMaterial'])

    .controller('LoginController', ['$scope', '$mdDialog', '$window', 'LoginService', function ($scope, $mdDialog, $window, LoginService) {
    
        $scope.titleButton = "INGRESAR";

        $scope.validate = function () {

            var validateData = {
                nickname: $scope.user,
                password: $scope.pass
            };

            LoginService.validate(validateData).then(function (result) {
                var status = parseInt(result.data.response);
                
                if (status == 1) {                    
                    $window.location.href = "../app";
                } else {

                    var message = "";
                    if (status == 0) {
                        message = "Contraseña Inválida";
                    } else if (status == -1) {
                        message = "El Usuario no tiene privilegios de acceso a este sitio";
                    } else if (status == -2) {
                        message = "Usuario Inválido";
                    }

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
                        .title(message)
                        .ariaLabel('Lucky day')
                        .targetEvent(id)
                        .ok('Aceptar');

                    $mdDialog.show(confirmResult).then(function() {

                    });
                   
                }

            });

        };

    }])