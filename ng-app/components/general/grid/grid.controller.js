function gridController() {
    var ctrl = this;

    ctrl.$onInit = function() {
        ctrl.columns.actions = "Acciones";        
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

}

grid.controller('gridController', [gridController])
    .filter('isDate', function ($filter) {
        return function (input) {
            var dateWrapper = new Date(input);
            if (!isNaN(dateWrapper.getTime()) && ((dateWrapper.getTime()).toString()).length == 13) {
                return $filter('date')(input, 'dd/MM/yyyy');
            } else {            
                return input;
            }
            
        }
    })
    .filter('isStatusLetter', function ($filter) {

        return function (input) {

            if ( input != null && (input.toString()).length == 1){

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
    })
    .filter('capitalizeEveryWord', function() {
      return function(input){        
        var text = input.toString();
        if (text != null) {


            if(text.indexOf(' ') !== -1){
              var inputPieces,
                  i;

              text = text.toLowerCase();
              inputPieces = text.split(' ');

              for(i = 0; i < inputPieces.length; i++){
                inputPieces[i] = capitalizeString(inputPieces[i]);
              }

              return inputPieces.toString().replace(/,/g, ' ');
            }
            else {
              text = text.toLowerCase();
              return capitalizeString(text);
            }
        }
        function capitalizeString(inputString){
          return inputString.substring(0,1).toUpperCase() + inputString.substring(1);
        }
      };
    });