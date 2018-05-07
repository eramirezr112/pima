angular.module('MainMenu', [])
.component('mainMenuPage', {
	bindings: {
	    codUser: '='
	},
	templateUrl: '../ng-app/components/general/main-menu/main-menu.html',
	controller: 'mainMenuController'
})
.controller('mainMenuController', [ 'MainMenuService', function (MainMenuService) {
	var ctrl = this;
	MainMenuService.getOptionsByUser().then(function (result) {		
		ctrl.menuOptions = result.data.menu_options;
	});

}]);

