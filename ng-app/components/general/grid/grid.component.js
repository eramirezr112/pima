var gridComponent = {
    bindings: {
    	title: '@',
    	subTitle: '@',
    	actions: '=',
        columns: '=',
        infoGrid: '=',
        locationPath: '='
    },
    templateUrl: '../ng-app/components/general/grid/grid.html',
    controller: 'gridController'
};

grid.component('grid', gridComponent); 