function gridController($timeout) {
    var ctrl = this;

    ctrl.$onInit = function() {
        ctrl.totalRegistros = ctrl.infoGrid.length;
        ctrl.columns[ctrl.columns.length] = {visible:true, text:"Acciones"};
        var firstRow = ctrl.infoGrid[0];
        
        ctrl.headerClass = {};
        for (r in firstRow){

            var colClass = 'col-align-';
            var align = 'center'            
            if (!isNaN(firstRow[r])) {
                colClass = colClass + "right";
            } else {
                colClass = colClass + "left";
            }

            ctrl.headerClass[r] = colClass;
        }

    };

    ctrl.getHeaderClass = function (key, index) {
        if (index > 0) {            
            return ctrl.headerClass[key];    
        } else {
            return 'col-align-center';
        }
    };

    ctrl.addClass = function(index, value) {
        var colClass = 'col-align-';
        var align = 'center'
        if (index > 0) {
            if (!isNaN(value)) {
                colClass = colClass + "right";
            } else {
                colClass = colClass + "left";
            }
        } else {
            colClass = colClass + align;
        }

        return colClass;
    };

    ctrl.approve = function (cod) {        
        ctrl.approveAction(cod);
    };

}

grid.controller('gridController', ['$timeout', gridController])
    .filter('isDate', function ($filter) {
        return function (input) {

            var formats = [
                moment.ISO_8601,
                "DD/MM/YYYY"
            ];

            var result = moment(input, formats, true).isValid();

            if (result) {
                return $filter('date')(input, 'dd/MM/yyyy');
            } else {
                return input;
            }
            /*
            var dateWrapper = new Date(input);
            if (!isNaN(dateWrapper.getTime()) && ((dateWrapper.getTime()).toString()).length == 13) {
                return $filter('date')(input, 'dd/MM/yyyy');
            } else {            
                return input;
            }
            */
        }
    })
    .filter('isStatusLetter', function ($filter) {

        return function (input) {

            if ( input != null && (input.toString()).length == 1 && !input.match(/^-{0,1}\d+$/)){

                var output = "";
                if (input === 'E') {
                    output = "Entregado";
                } else if (input === 'C') {
                    output = "Confección";
                } else if (input === 'N') {
                    output = "Anuladas";
                } else if (input === 'P') {
                    output = "Préstamo";
                } else if (input === 'A') {
                    output = "Aprobadas";
                }

                return output;

            } else {
                return input;
            }
        }
    });