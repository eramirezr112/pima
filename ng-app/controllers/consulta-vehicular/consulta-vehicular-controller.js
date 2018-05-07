angular.module('ConsultaVehicular', ['ui.bootstrap'])
    .controller('ConsultaVehicularController', ['$scope', '$uibModal', 'usuario', 'provincias', 'cantones', 'periodos', 'consultaVehicular', function ($scope, $uibModal, usuario, provincias, cantones, periodos, consultaVehicular) {

        $scope.title = 'Consulta Vehicular (día E)'
        $scope.access = usuario.data.usuario.ind_presup;

        $scope.provincias = provincias.data;        
        $scope.cantones = cantones.data;
        $scope.periodos = periodos.data.periodos;
        $scope.periodo = periodos.data.periodos[0];

        $scope.consulta = consultaVehicular.data;

        $scope.resetCantones = function () {
            $scope.canton = null;
            $scope.cantones = cantones.data;
        };        

        $scope.viewDocument = function (index, codConsulta, type) {            

            var baseTemplatePath = '../ng-app/views/consulta-vehicular/';
            var modalData = getModalData(type);
            $scope.opts = {
                backdrop: true,
                backdropClick: true,
                dialogFade: false,
                keyboard: true,
                templateUrl : baseTemplatePath + modalData.template,
                controller : modalData.controller,
                windowClass: 'app-modal-window',
                resolve: modalData.resolve
            };
        
            var modalInstance = $uibModal.open($scope.opts);

            modalInstance.result.then(function() {
                //$scope.activeRowTab = index;
                //console.log("Modal Opened!");
            });

            /**
             * Get document's data for the Modal Box
             */
            function getModalData(typeDoc) {

                var template = '';
                var controller = '';
                var resolve = {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    periodo: function (PeriodoService) {
                        return PeriodoService.get();
                    }
                };

                switch (type) {
                    // View Consulta Vehicular
                    case 1:
                        template = "view.html";
                        controller = "ViewConsultaVehicularController";
                        resolve.consultaData = function (ConsultaVehicularService, $route) {
                            return ConsultaVehicularService.get(codConsulta);
                        };
                        break;
                }

                var data = {
                    template: template,
                    controller: controller,
                    resolve: resolve
                };

                return data;
            };

        }        

    }]);