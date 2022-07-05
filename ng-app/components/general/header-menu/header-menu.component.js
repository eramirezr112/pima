angular.module('HeaderMenu', ['ngMaterial'])
.component('headerMenuPage', {
	bindings: {
	    nameUser: '@',
	    profilePhoto: '@',
	    profileData: '=',
	},
	templateUrl: '../ng-app/components/general/header-menu/header-menu.html?v='+session,
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
			  templateUrl: '../ng-app/components/general/header-menu/close-session.tmpl.html?v='+session,
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
	};

	ctrl.loadProfileInfo = function (ev) {
		
			$mdDialog.show({	
			  controller: DialogProfileController,		  
			  templateUrl: '../ng-app/components/general/header-menu/profile-page.tmpl.html?v='+session,
			  parent: angular.element(document.body),
			  targetEvent: ev,
			  clickOutsideToClose:true,
			  fullscreen: $scope.customFullscreen // Only for -xs, -sm breakpoints.
			});

		  function DialogProfileController($scope, $mdDialog) {

		  	$scope.profile = ctrl.profileData;
		  	$scope.photo = ctrl.profilePhoto;

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
	};

}]);