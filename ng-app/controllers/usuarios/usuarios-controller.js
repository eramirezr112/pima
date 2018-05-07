angular.module('Usuarios', ['ui.bootstrap', 'angularUtils.directives.dirPagination'])
    .controller('UsuariosController', ['$scope', '$timeout', '$uibModal', 'usuario', 'listUsuarios', 'listRoles', 'UsuarioService', function ($scope, $timeout, $uibModal, usuario, listUsuarios, listRoles, UsuarioService) {

        $scope.title = 'Administración de Usuarios';

		
        $scope.usuarios = listUsuarios.data;
        console.log($scope.usuarios);
        $scope.roles    = listRoles.data;        

        //Permiso de Usuario
        $scope.access = true;
        $scope.rowChange = true;
        $scope.isPermitsAssigned = false;
        $scope.isLoading = false;

        $scope.changeRole = function (obj, codUser, selectedRol) {
			$scope.usuarios[obj].isChange = true;
        };

        $scope.updateUserRol = function (obj, codUser) {

        	if($scope.usuarios[obj].rol != null) {

	        	var data = {
	        		codUser: parseInt(codUser),
	        		rolUser: parseInt($scope.usuarios[obj].rol.rol_usuario)
	        	};
				UsuarioService.updateUserRol(data).then(function (result) { 
					if (result.data == "1"){
						alert("El rol del usuario fue actualizado");
						$scope.usuarios[obj].isChange = 'false';
					} else {
						alert("El rol del usuario NO pudo ser actualizado");
					}

				});

        	} else {
        		alert("Debe de seleccionar al menos un rol");
        	}

        };

    }]);