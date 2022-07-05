angular
  .module("Login", ["ngMaterial"])

  .controller("LoginController", [
    "$scope",
    "$mdDialog",
    "$window",
    "LoginService",
    function ($scope, $mdDialog, $window, LoginService) {
      $scope.titleButton = "INGRESAR";

      $scope.validate = function () {
        var validateData = {
          nickname: $scope.user,
          password: $scope.pass,
        };

        LoginService.validate(validateData).then(function (result) {
          var status = parseInt(result.data.response);
          console.log(result.data);
          if (status == 1) {
            $window.location.href = "../app";
          } else {
            var message = "";
            if (status == 0) {
              message = "Contraseña Inválida";
            } else if (status == -1) {
              message =
                "El Usuario no tiene privilegios de acceso a este sitio";
            } else if (status == -2) {
              message = "Usuario Inválido";
            } else if (status == -3) {
              message =
                "Este usuario Ya tiene una sesión activa. ¿Desea Continuar?";
            }

            var id = 17;
            if (status != -3) {
              var confirmResult = $mdDialog
                .confirm({
                  onComplete: function afterShowAnimation() {
                    var $dialog = angular.element(
                      document.querySelector("md-dialog")
                    );
                    var $actionsSection = $dialog.find("md-dialog-actions");
                    var $cancelButton = $actionsSection.children()[0];
                    var $confirmButton = $actionsSection.children()[1];
                    angular
                      .element($confirmButton)
                      .addClass("btn-accept md-raised");
                  },
                })
                .title(message)
                .ariaLabel("Lucky day")
                .targetEvent(id)
                .ok("Aceptar");

              $mdDialog.show(confirmResult).then(function () {});
            } else {
              var cod = parseInt(result.data.cod);

              var confirm = $mdDialog
                .confirm({
                  onComplete: function afterShowAnimation() {
                    var $dialog = angular.element(
                      document.querySelector("md-dialog")
                    );
                    var $actionsSection = $dialog.find("md-dialog-actions");
                    var $cancelButton = $actionsSection.children()[0];
                    var $confirmButton = $actionsSection.children()[1];
                    angular
                      .element($confirmButton)
                      .addClass("btn-accept md-raised");
                    angular
                      .element($cancelButton)
                      .addClass("btn-cancel md-raised");
                  },
                })
                .title(message)
                .ariaLabel("Lucky day")
                .targetEvent(id)
                .ok("Si")
                .cancel("No");

              $mdDialog.show(confirm).then(
                function () {
                  console.log("INGRESA!");

                  var newValidateData = {
                    nickname: $scope.user,
                    password: $scope.pass,
                    cod: cod,
                  };

                  LoginService.validate(newValidateData).then(function (
                    result
                  ) {
                    console.log(result);
                    var status = parseInt(result.data.response);

                    if (status == 1) {
                      $window.location.href = "../app";
                    }
                  });

                  /*
                            ViaticosService.getNumComprobante(id).then(function (viaticoData) {

                                var viaticoInfo = viaticoData.data.encabezado[0];

                                var codSolicitud = id;
                                var codCentro    = viaticoInfo.cod_centro_costo;
                                var codMeta      = viaticoInfo.cod_meta;
                                var monto        = viaticoInfo.mon_comprobante;

                                ViaticosService.approveLiquidacionViaticos(codSolicitud, codCentro, codMeta, monto).then(function (result) {
                                   
                                    var response = result.data.response;

                                    if (response == 1) {

                                        var confirmResult = $mdDialog.confirm({
                                                onComplete: function afterShowAnimation() {
                                                    var $dialog = angular.element(document.querySelector('md-dialog'));
                                                    var $actionsSection = $dialog.find('md-dialog-actions');
                                                    var $cancelButton = $actionsSection.children()[0];
                                                    var $confirmButton = $actionsSection.children()[1];
                                                    angular.element($confirmButton).addClass('btn-accept md-raised');
                                                }
                                            })
                                            .title('La solicitud fue aprobada satisfactoriamente')
                                            .ariaLabel('Lucky day')
                                            .targetEvent(id)
                                            .ok('Aceptar');

                                        $mdDialog.show(confirmResult).then(function() {

                                            ViaticosService.getAllLiquidacionViaticos().then(function (liquidacionViaticos) {
                                                $scope.liquidacionViaticos = liquidacionViaticos.data.liquidacionViaticos;
                                            });                                

                                        });

                                    } else if (response < 0) {

                                        var confirmResult = $mdDialog.confirm({
                                                onComplete: function afterShowAnimation() {
                                                    var $dialog = angular.element(document.querySelector('md-dialog'));
                                                    var $actionsSection = $dialog.find('md-dialog-actions');
                                                    var $cancelButton = $actionsSection.children()[0];
                                                    var $confirmButton = $actionsSection.children()[1];
                                                    angular.element($confirmButton).addClass('btn-accept md-raised');
                                                }
                                            })
                                            .title('No se puede aprobar la solicitud debido a que no existe presupuesto disponible.')
                                            .ariaLabel('Lucky day')
                                            .targetEvent(id)
                                            .ok('Aceptar');

                                        $mdDialog.show(confirmResult).then(function() {
                                            
                                            return;

                                        });                        
                                    } else {

                                        var confirmResult = $mdDialog.confirm({
                                                onComplete: function afterShowAnimation() {
                                                    var $dialog = angular.element(document.querySelector('md-dialog'));
                                                    var $actionsSection = $dialog.find('md-dialog-actions');
                                                    var $cancelButton = $actionsSection.children()[0];
                                                    var $confirmButton = $actionsSection.children()[1];
                                                    angular.element($confirmButton).addClass('btn-accept md-raised');
                                                }
                                            })
                                            .title('HA OCURRIDO UN ERROR: La solicitud No puede aprobarse en estos momentos')
                                            .ariaLabel('Lucky day')
                                            .targetEvent(id)
                                            .ok('Aceptar');

                                        $mdDialog.show(confirmResult).then(function() {
                                            
                                            return;

                                        });                        
                                    }

                                });                    

                            });
                            */
                },
                function () {
                  console.log("NO INGRESA!");
                }
              );
            }
          }
        });
      };
    },
  ]);
