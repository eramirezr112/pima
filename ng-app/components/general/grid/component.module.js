var moduleDependencies = [];
var grid = angular.module('grid', moduleDependencies);

/*
angular.module('Grid', moduleDependencies)
.component('headerMenuPage', {
	bindings: {
	    nameUser: '@'
	},
	templateUrl: '../ng-app/components/general/header-menu/header-menu.html',
	controller: 'headerMenuController'
})
.controller('headerMenuController', [ 'HeaderMenuService', function (HeaderMenuService) {
	var ctrl = this;
	console.log("Cargando Header...");

	HeaderMenuService.getOptionsByUser().then(function (result) {
		//console.log(result);
		ctrl.menuOptions = result.data.menu_options;
	});

}]);
*/