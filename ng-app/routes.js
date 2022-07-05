angular.module("Routes", ['ngRoute'])

    .run(function($rootScope, $templateCache) {
        $rootScope.$on('$routeChangeStart', function(event, next, current) {
            if (typeof(current) !== 'undefined'){
                $templateCache.remove(current.templateUrl);
            }
        });
    })

    .config(['$routeProvider', function($routeProvider){

        $routeProvider
            .when('/home', {
                templateUrl: '../ng-app/views/home/index.html?v='+session,
                controller: 'HomeController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                }                
            })
            .when('/solicitud', {
                templateUrl: '../ng-app/views/solicitud/list.html?v='+session,
                controller: 'SolicitudController',
                resolve: {
                    solicitudes: function(SolicitudService){
                        return SolicitudService.all();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                }                
            })
            .when('/solicitud/view/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud/view.html?v='+session,
                controller: 'ViewSolicitudController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    solicitudData: function (SolicitudService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudService.get(idSolicitud);
                        //return 0;
                    }
                    /*,
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    */
                }
            })
            .when('/vacaciones', {
                templateUrl: '../ng-app/views/vacaciones/list.html?v='+session,
                controller: 'SolicitudVacacionesController',
                resolve: {
                    vacaciones: function(VacacionesService){
                        return VacacionesService.all();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    }
                }                
            })
            .when('/vacaciones/view/:idSolicitud', {
                templateUrl: '../ng-app/views/vacaciones/view.html?v='+session,
                controller: 'ViewVacacionController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    vacacionData: function (VacacionesService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return VacacionesService.get(idSolicitud);

                    }
                }
            })
            .when('/adelanto-viaticos', {
                templateUrl: '../ng-app/views/adelanto-viaticos/list.html?v='+session,
                controller: 'AdelantoViaticosController',
                resolve: {
                    adelantoViaticos: function(ViaticosService){
                         return ViaticosService.getAllAdelantoViaticos();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    }
                }
            })
            .when('/adelanto-viaticos/view/:numAdelanto', {
                templateUrl: '../ng-app/views/adelanto-viaticos/view.html?v='+session,
                controller: 'ViewAdelantoViaticosController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    viaticoData: function (ViaticosService, $route) {
                        var numAdelanto = $route.current.params.numAdelanto;                        
                        return ViaticosService.getNumAdelanto(numAdelanto);

                    }                    
                }
            })
            .when('/liquidacion-viaticos', {
                templateUrl: '../ng-app/views/liquidacion-viaticos/list.html?v='+session,
                controller: 'LiquidacionViaticosController',
                resolve: {
                    liquidacionViaticos: function(ViaticosService){
                         return ViaticosService.getAllLiquidacionViaticos();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    }
                }
            })
            .when('/liquidacion-viaticos/view/:numComprobante', {
                templateUrl: '../ng-app/views/liquidacion-viaticos/view.html?v='+session,
                controller: 'ViewLiquidacionViaticosController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    viaticoData: function (ViaticosService, $route) {
                        var numComprobante = $route.current.params.numComprobante;
                        return ViaticosService.getNumComprobante(numComprobante);

                    }                    
                }
            })
            .when('/ejecucion-presupuestaria', {
                templateUrl: '../ng-app/views/ejecucion-presupuestaria/index.html?v='+session,
                controller: 'EjecucionPresupuestariaController',
                resolve: {
                    /*
                    programas: function(ProgramaService){
                        return ProgramaService.all();
                    },*/
                    centroCostos: function (PresupuestoService) {
                        return PresupuestoService.getAllCentroCostos();
                    },
                    listYears: function (PresupuestoService) {
                        return PresupuestoService.getYears();
                    },                    
                    presupuesto: function (PresupuestoService) {
                        var year          = new Date().getFullYear();
                        var codCentro     = '';
                        var codSubpartida = '';
                        var desCuenta     = '';
                        return PresupuestoService.getEncabezado(year, codCentro, codSubpartida, desCuenta);
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    }                         
                }
                
            })
            .when('/solicitud-recursos-aj', {
                templateUrl: '../ng-app/views/solicitud-recursos/list-solicitudes.html?v='+session,
                controller: 'SolicitudRecursosController',
                resolve: {
                    solicitudes: function(SolicitudRecursosService){
                        return SolicitudRecursosService.all(1);
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                }                
            })
            .when('/solicitud-recursos-ag', {
                templateUrl: '../ng-app/views/solicitud-recursos/list-solicitudes.html?v='+session,
                controller: 'SolicitudRecursosController',
                resolve: {
                    solicitudes: function(SolicitudRecursosService){
                        return SolicitudRecursosService.all(2);
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                }                
            })
            .when('/solicitud-recursos-al', {
                templateUrl: '../ng-app/views/solicitud-recursos/list-solicitudes.html?v='+session,
                controller: 'SolicitudRecursosController',
                resolve: {
                    solicitudes: function(SolicitudRecursosService){
                        return SolicitudRecursosService.all(12);
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                }                
            })
            .when('/solicitud-recursos-aj/view/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud-recursos/view-recurso-aj.html?v='+session,
                controller: 'ViewSolicitudRecursosAJController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    solicitudData: function (SolicitudRecursosService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudRecursosService.get(idSolicitud);
                        //return 0;
                    }
                    /*,
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    */
                }
            })
            .when('/solicitud-recursos-ag/view/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud-recursos/view-recurso-ag.html?v='+session,
                controller: 'ViewSolicitudRecursosAGController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    solicitudData: function (SolicitudRecursosService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudRecursosService.get(idSolicitud);
                        //return 0;
                    }
                    /*,
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    */
                }
            })
            .when('/solicitud-recursos-al/view/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud-recursos/view-recurso-al.html?v='+session,
                controller: 'ViewSolicitudRecursosALController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    solicitudData: function (SolicitudRecursosService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudRecursosService.get(idSolicitud);
                        //return 0;
                    }
                    /*,
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    */
                }
            })
            /*
            .when('/solicitud/add', {
                templateUrl: '../ng-app/views/solicitud/add.html',
                controller: 'AddSolicitudController',
                resolve: {
                    programas: function(ProgramaService){
                        return ProgramaService.get();
                    },
                    proveedores: function(ProveedorService){
                        return ProveedorService.all();
                    },
                    periodo: function (PeriodoService) {
                        return PeriodoService.get();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    lastSolicitud: function (SolicitudService) {
                        return SolicitudService.getMaxNum();
                    },
                    tipoCambio: function (SolicitudService) {                        
                        return SolicitudService.getTipoCambio();
                    }
                }
            })
            .when('/solicitud/edit/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud/edit.html',
                controller: 'EditSolicitudController',
                resolve: {
                    programas: function(ProgramaService){
                        return ProgramaService.get();
                    },
                    proveedores: function(ProveedorService){
                        return ProveedorService.all();
                    },
                    periodo: function (PeriodoService) {
                        return PeriodoService.get();
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    solicitudData: function (SolicitudService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudService.get(idSolicitud);
                    },
                    tipoCambio: function (SolicitudService) {                        
                        return SolicitudService.getTipoCambio();
                    }
                }
            })
            .when('/solicitud/view/:idSolicitud', {
                templateUrl: '../ng-app/views/solicitud/view.html',
                controller: 'ViewSolicitudController',
                resolve: {
                    periodo: function (PeriodoService) {
                        return PeriodoService.get();
                    },
                    solicitudData: function (SolicitudService, $route) {
                        var idSolicitud = $route.current.params.idSolicitud;                        
                        return SolicitudService.get(idSolicitud);
                    },
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                }
            })
            .when('/ejecucion-presupuestaria', {
                templateUrl: '../ng-app/views/ejecucion-presupuestaria/index.html',
                controller: 'EjecucionPresupuestariaController',
                resolve: {
                    programas: function(ProgramaService){
                        return ProgramaService.all();
                    },
                    periodos: function (PeriodoService) {
                        return PeriodoService.all();
                    },
                    cuentas: function (CuentaService) {
                        return CuentaService.all();
                    },
                    presupuesto: function (PresupuestoService) {
                        var codPrograma = 0;
                        var codPartida  = 0;
                        var codEstado   = 1;
                        return PresupuestoService.getEncabezado(codPrograma, codPartida, codEstado);
                    },
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },                         
                }
                
            })
            .when('/consulta-vehicular', {
                templateUrl: '../ng-app/views/consulta-vehicular/index.html',
                controller: 'ConsultaVehicularController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    provincias: function (ProvinciaService) {
                        return ProvinciaService.all();
                    },
                    cantones: function (CantonService) {
                        return CantonService.all();
                    },
                    periodos: function (PeriodoService) {
                        return PeriodoService.all();
                    },
                    consultaVehicular: function (ConsultaVehicularService) {
                        return ConsultaVehicularService.all();
                    },
                }                
            })
            .when('/roles', {
                templateUrl: '../ng-app/views/roles/index.html',
                controller: 'RolesController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    listRoles: function (SistemaService) {
                        return SistemaService.getRoles();
                    },
                    listPermisos: function (SistemaService) {
                        return SistemaService.getPermisos();
                    }
                }                
            })
            .when('/roles/add', {
                templateUrl: '../ng-app/views/roles/add.html',
                controller: 'AddRolController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    listRoles: function (SistemaService) {
                        return SistemaService.getRoles();
                    },
                    listPermisos: function (SistemaService) {
                        return SistemaService.getPermisos();
                    }
                }                
            })
            .when('/roles/edit/:idRol', {
                templateUrl: '../ng-app/views/roles/edit.html',
                controller: 'EditRolController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    listRoles: function (SistemaService) {
                        return SistemaService.getRoles();
                    },
                    rolData: function (SistemaService, $route) {
                        var idRol = $route.current.params.idRol;                        
                        return SistemaService.getInfoRol(idRol);
                    },
                    listPermisos: function (SistemaService) {
                        return SistemaService.getPermisos();
                    }
                }
            })
            .when('/usuarios', {
                templateUrl: '../ng-app/views/usuarios/list.html',
                controller: 'UsuariosController',
                resolve: {
                    usuario: function (UsuarioService) {
                        return UsuarioService.get();
                    },
                    listUsuarios: function (UsuarioService) {
                        UsuarioService.all().then(function (r) {
                           console.log(r);
                        });
                        return UsuarioService.all();                        
                    },
                    listRoles: function (SistemaService) {
                        return SistemaService.getRoles();
                    }
                }                
            })*/
            .otherwise('/home');

    }])
    .directive('activeLink', ['$location', function (location) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs, controller) {
            var clazz = attrs.activeLink;            
            var path = attrs.href;            
            path = path.substring(2); //hack because path does not return including hashbang            
            scope.location = location;            
            scope.$watch('location.path()', function (newPath) {             
              if (path === newPath) {
                element.addClass(clazz);
              } else {
                element.removeClass(clazz);
              }
            });
          }
        };
      }])    
    .directive('showDuringResolve', function($rootScope, $timeout) {

      return {
        link: function(scope, element) {

          $rootScope.$on('$routeChangeStart', function(event, currentRoute, previousRoute) {            

            $timeout(function() {
              element.removeClass('ng-hide');
            });
          });

          $rootScope.$on('$routeChangeSuccess', function() {
            element.addClass('ng-hide');
          });

        }
      };
    })    
    .directive('resolveLoader', function($rootScope, $timeout) {

      return {
        restrict: 'E',
        replace: true,        
        template: '<div class="loading-cover-page ng-hide"><div class="loading-contaider"><img src="../web/img/ajax-loader.gif" alt="" class="image-loader"></div></div>',
        link: function(scope, element) {

          $rootScope.$on('$routeChangeStart', function(event, currentRoute, previousRoute) {            
            if (previousRoute) return;

            $timeout(function() {
              element.removeClass('ng-hide');
            });
          });

          $rootScope.$on('$routeChangeSuccess', function() {
            element.addClass('ng-hide');
          });
        }
      };
    });