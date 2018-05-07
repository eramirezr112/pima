angular.module('HeaderMenu', [])
.component('headerMenuPage', {
	bindings: {
	    nameUser: '@'
	},
	templateUrl: '../ng-app/components/general/header-menu/header-menu.html',
	controller: 'headerMenuController'
})
.controller('headerMenuController', [ 'HeaderMenuService', function (HeaderMenuService) {
	var ctrl = this;
	HeaderMenuService.getOptionsByUser().then(function (result) {
		//console.log(result);
		ctrl.menuOptions = result.data.menu_options;
	});

}]);