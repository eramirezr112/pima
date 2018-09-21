angular.module("VacacionesService", []).factory("VacacionesService", function($http){

    var apiBase = '../app/api.php?c=';
    var controllerName = 'vacacion';
    var path = apiBase + controllerName;

    return {
        all: function(){
            var action = 'all';
            var config = {
                headers : {'Accept' : 'application/json'}
            };  
            //console.log(path+'&f='+action);          
            return $http.get(path+'&f='+action, config);
        },
        getGuardadas: function(){
            var action = 'getGuardadas';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);
        },
        add: function (solicitud) {
            var action = 'add';
            var data = {
                encabezado: solicitud.encabezado,
                detalle: solicitud.detalle
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        update: function (solicitud) {
            var action = 'update';
            var data = {
                encabezado: solicitud.encabezado,
                detalle: solicitud.detalle
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        get: function (idSolicitud) {
            var action = 'get';
            var data = {
                idSolicitud: idSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        approve: function (solicitud) {
            var action = 'approve';
            var data = {
                newSaldoPeriodo: solicitud.newSaldoPeriodo,
                codFuncionario: solicitud.codFuncionario,
                numSolicitud: solicitud.numSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        getMaxNum: function () {
            var action = 'getMaxNum';
            var config = {                
                headers : {'Accept' : 'application/json'}
            };              
            return $http.get(path+'&f='+action, config);
        },
        changeStatus: function (codSolicitud, nStatus){
            var action = 'changeStatus';
            var data = {
                codSolicitud: codSolicitud,
                nStatus: nStatus
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };
            return $http.get(path+'&f='+action, config);
        },
        getTipoCambio: function () {
            var action = 'getTipoCambio';
            var config = {
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        },
        verifySolicitud: function (codSolicitud) {
            var action = 'verifySolicitud';
            var data = {
                codSolicitud: codSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        },
        startAfectacionPresupuestaria: function (codSolicitud, codPeriodo, codPrograma, moneda) {
            var action = 'startAfectacionPresupuestaria';
            var data = {
                codSolicitud: codSolicitud,
                codPeriodo: codPeriodo, 
                codPrograma: codPrograma, 
                moneda: moneda
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        },
        rejectAfectacionPresupuestaria: function (codSolicitud) {
            var action = 'rejectAfectacionPresupuestaria';
            var data = {
                codSolicitud: codSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        },
        setCompromisoAprobado: function (codSolicitud) {
            var action = 'setCompromisoAprobado';
            var data = {
                codSolicitud: codSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        },
        validateFactura: function (codSolicitud) {
            var action = 'validateFactura';
            var data = {
                codSolicitud: codSolicitud
            };
            var config = {
                params: data,
                headers : {'Accept' : 'application/json'}
            };            
            return $http.get(path+'&f='+action, config);  
        }
    };
});