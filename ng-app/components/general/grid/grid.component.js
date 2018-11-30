var gridComponent = {
    bindings: {
    	title: '@',
    	subTitle: '@',
    	actions: '=',
        columns: '=',
        infoGrid: '=',
        locationPath: '=',
        approveAction: '&'
    },
    templateUrl: '../ng-app/components/general/grid/grid.html?s='+session,
    controller: 'gridController'
};

grid.component('grid', gridComponent); 