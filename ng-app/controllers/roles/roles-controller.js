angular.module('Roles', ['ui.bootstrap', 'checklist-model'])
    .controller('RolesController', ['$scope', '$timeout', '$window', '$uibModal', 'usuario', 'listRoles', 'listPermisos', 'UsuarioService', function ($scope, $timeout, $window, $uibModal, usuario, listRoles, listPermisos, UsuarioService) {

        $scope.title = 'Administración de Roles';

        //$scope.usuarios = listUsuarios.data;
        $scope.roles    = listRoles.data;
        $scope.permisos = listPermisos.data;

        //Permiso de Usuario
        $scope.access = true;
        $scope.isSuperAdmin = false;
        $scope.isPermitsAssigned = false;
        $scope.isLoading = false;

         //create a blank array to store selected objects.
        var selModulos = [];
		for (var i in listPermisos.data) {

		  var objX = listPermisos.data[i];
		  var newObjX = {};
		  for (var n in objX) {
		  	if (n != 'opciones_modulo') {
		  		newObjX[n] = objX[n];	
		  	} else {
		  		newObjX.opciones_modulo = [];
		  	}
		  	newObjX.checkAll = false;
		  }

		  selModulos.push(newObjX);

		}

		$scope.selectedModulos = selModulos;
		
		$scope.checkAll = function(modulo) {			
			$scope.selectedModulos[modulo].opciones_modulo = angular.copy($scope.permisos[modulo].opciones_modulo);
			$scope.selectedModulos[modulo].checkAll = true;
		};
		
		$scope.uncheckAll = function(modulo) {
		  $scope.selectedModulos[modulo].opciones_modulo = [];
		  $scope.selectedModulos[modulo].checkAll = false;
		};

		function merge_options(obj1,obj2){
		    var obj3 = $scope.selectedModulos;
		    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
		    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
		    return obj3;
		}

		$scope.loadPermitsByUser = function (nRol) {

			$scope.isLoading = true;
			var codUser = $scope.usuario.info.codigo;
			UsuarioService.getPermisionByUser(codUser).then(function (result) {				
				
				if (result.data.permisos != null) {

					$scope.isPermitsAssigned = true;
					if (nRol == 0) {
						$scope.usuario.rol = $scope.roles[result.data.rol-1];
					} else {
						$scope.usuario.rol = $scope.roles[nRol-1];
					}					

					if (result.data.rol > 1) {
						$scope.isSuperAdmin = false;
						merge_options($scope.selectedModulos, result.data.permisos);
					} else {
						$scope.isSuperAdmin = true;
						for(sm in $scope.selectedModulos){
							$scope.selectedModulos[sm].opciones_modulo = angular.copy($scope.permisos[sm].opciones_modulo);
							$scope.selectedModulos[sm].checkAll = true;
						}						
					}
									
				} else {

					$scope.isPermitsAssigned = false;
					$scope.usuario.rol = null;

			         //create a blank array to store selected objects.
			        var selModulos = [];
					for (var i in listPermisos.data) {

					  var objX = listPermisos.data[i];
					  var newObjX = {};
					  for (var n in objX) {
					  	if (n != 'opciones_modulo') {
					  		newObjX[n] = objX[n];	
					  	} else {
					  		newObjX.opciones_modulo = [];
					  	}
					  	newObjX.checkAll = false;
					  }

					  selModulos.push(newObjX);

					}

					$scope.selectedModulos = selModulos;

				}
				
				$scope.isLoading = false;
			});
			

		};

		$scope.loadPermitsByRole = function () {			
			if ($scope.isPermitsAssigned == false && $scope.usuario.rol.rol_usuario == 1) {

				for (var m in $scope.selectedModulos) {					
					
					$scope.selectedModulos[m].opciones_modulo = angular.copy($scope.permisos[m].opciones_modulo);
					$scope.selectedModulos[m].checkAll = true;
				}
				$scope.isSuperAdmin = true;

			} else {

				if ($scope.isPermitsAssigned == true && $scope.usuario.rol.rol_usuario == 1) {

					$scope.isSuperAdmin = true;
					for (var m in $scope.selectedModulos) {					
						
						$scope.selectedModulos[m].opciones_modulo = angular.copy($scope.permisos[m].opciones_modulo);
						$scope.selectedModulos[m].checkAll = true;
					}
					
				} else {

					$scope.isSuperAdmin = false;
					if ($scope.isPermitsAssigned == false) {
						for (var m in $scope.selectedModulos) {					
							
							$scope.selectedModulos[m].opciones_modulo = [];
							$scope.selectedModulos[m].checkAll = false;
						}
					} else {

				         //create a blank array to store selected objects.
				        var selModulos = [];
						for (var i in listPermisos.data) {

						  var objX = listPermisos.data[i];
						  var newObjX = {};
						  for (var n in objX) {
						  	if (n != 'opciones_modulo') {
						  		newObjX[n] = objX[n];	
						  	} else {
						  		newObjX.opciones_modulo = [];
						  	}
						  	newObjX.checkAll = false;
						  }

						  selModulos.push(newObjX);

						}

						$scope.selectedModulos = selModulos;

						$scope.loadPermitsByUser($scope.usuario.rol.rol_usuario);
						$timeout(function() { $scope.isSuperAdmin = false;}, 2000);
					}

				}

			}
		};

		$scope.savePermits = function () {
			
			var data = {
				codUser: parseInt($scope.usuario.info.codigo),
				rolUser: parseInt($scope.usuario.rol.rol_usuario),
				permits: $scope.selectedModulos
			};

			UsuarioService.savePermits(data).then(function (result) {
				if (result.data == 1) {
					alert("Los permisos de usuario se actualizaron correctamente!");
				} else {
					alert("Se le han asignado al usuario seleccionado los permisos de Acceso!");
				}
			});

		};

		//Delete Rol
		$scope.deleteRol = function (rol) {
			UsuarioService.deleteRol(rol).then(function (r) {

				if(r.data == 1) {
					alert("El rol fue eliminado correctamente");
					$window.location.reload();
				} else {
					alert("El rol no pudo ser eliminado");
				}

			});
		};


    }]);