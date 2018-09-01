angular.module('HeaderMenu', ['ngMaterial'])
.component('headerMenuPage', {
	bindings: {
	    nameUser: '@'
	},
	templateUrl: '../ng-app/components/general/header-menu/header-menu.html',
	controller: 'headerMenuController'
})
.controller('headerMenuController', ['$scope', '$mdDialog', '$location', 'HeaderMenuService', function ($scope, $mdDialog, $location, HeaderMenuService) {
	var ctrl = this;
	HeaderMenuService.getOptionsByUser().then(function (result) {
		//console.log(result);
		ctrl.menuOptions = result.data.menu_options;
	});

	ctrl.closeSession = function (ev) {
		
			$mdDialog.show({
			  controller: DialogController,
			  templateUrl: '../ng-app/components/general/header-menu/close-session.tmpl.html',
			  parent: angular.element(document.body),
			  targetEvent: ev,
			  clickOutsideToClose:true,
			  fullscreen: $scope.customFullscreen // Only for -xs, -sm breakpoints.
			})
			.then(function(answer) {			  
			  window.location = '../security/logout.php';
			}, function() {
				return;			  
			});

		  function DialogController($scope, $mdDialog) {
		    $scope.hide = function() {
		      $mdDialog.hide();
		    };

		    $scope.cancel = function() {
		      $mdDialog.cancel();
		    };

		    $scope.answer = function(answer) {
		      $mdDialog.hide(answer);
		    };
		  }			
		/*	
        var confirm = $mdDialog.confirm({
                onComplete: function afterShowAnimation() {
                    var $dialog = angular.element(document.querySelector('md-dialog'));                    
                    var $actionsSection = $dialog.find('md-dialog-actions');
                    var $contentSection = $dialog.find('md-dialog-content');
                    var $cancelButton = $actionsSection.children()[0];
                    var $confirmButton = $actionsSection.children()[1];
                    angular.element($contentSection).css('text-align', 'center');
                    angular.element($actionsSection).css('text-align', 'left');
                    angular.element($confirmButton).addClass('btn-accept md-raised');
                    angular.element($cancelButton).addClass('btn-cancel md-raised');
                }
            })
            .title('Cerrar Sessión')
            .ariaLabel('Lucky day')
            .targetEvent(ev)
            .ok('Si')
            .cancel('No');

        $mdDialog.show(confirm).then(function() {
            console.log("Aprueba Solicitud!");               
        }, function() {
            return;
        });	
        */	
	};

}]);