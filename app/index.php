<?php include("../security/checkSecurity.php"); 

/*
echo("<pre>");
print_r($_SESSION);
echo("</pre>");
exit;
*/

?>
<!DOCTYPE html>
<html lang="en" ng-app="pnlsys">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">    
    <link rel="icon" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-32x32.png" sizes="32x32">
    <link rel="icon" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-192x192.png" sizes="192x192">
    <link rel="apple-touch-icon-precomposed" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-180x180.png">

    <title>PIMA | Autorizaciones</title>

    <!-- Bootstrap core CSS -->
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="../node_modules/angular-material/angular-material.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="../web/css/home.css">
    <link rel="stylesheet" href="../web/css/table-style.css">
    <link rel="stylesheet" href="../web/css/custom-pagination.css">
    <link rel="stylesheet" href="../web/css/tab-style.css">
    <link rel="stylesheet" href="../web/css/custom-styles.css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  </head>

  <body>
    <!--
    <resolve-loader></resolve-loader>
    -->
    <div show-during-resolve class="loading-cover-page ng-hide"><div class="loading-contaider"><img src="../web/img/ajax-loader.gif" alt="" class="image-loader"></div></div>
    <nav class="navbar navbar-inverse navbar-fixed-top" style="background:#222627;">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#" style="color:#fff;">
            <img src="../web/img/logo-color.png" alt="" width="48px" style="margin-top:-3px; background:#222627;  padding:0px; position:">
          </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <header-menu-page 
            profile-photo="<?php if($_SESSION['IMG_FOTO'] != '') { echo base64_encode($_SESSION['IMG_FOTO']); } else { echo ''; } ?>" 
            name-user="<?php if ($_SESSION['CONNECTION_TYPE'] == 'odbc_mssql') { echo utf8_encode($_SESSION["des_usuario"]); } else { echo $_SESSION["des_usuario"]; } ?> (<?php echo $_SESSION["TIPO_FUNCIONARIO"]; ?>)"
            profile-data='<?php  echo json_encode($_SESSION["PROFILE_DATA"]); ?>'
          >
          </header-menu-page>
        </div>
      </div>
    </nav>

    <div class="container-fluid" style="padding-top: 50px;">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">          
          <main-menu-page cod-empleado="<?php echo trim($_SESSION["COD_EMPLEADO"]); ?>" cod-user="<?php echo $_SESSION["cod_usuario"]; ?>"></main-menu-page>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" style="padding-top:40px;">
          <div ng-view class="view-animate"></div>
        </div>
      </div>
    </div>
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script type="text/javascript" src="../web/js/moment.min.js"></script>
  <script type="text/javascript" src="../node_modules/angular/angular.min.js"></script>
  <script type="text/javascript" src="../node_modules/angular-animate/angular-animate.min.js"></script>
  <script type="text/javascript" src="../node_modules/angular-aria/angular-aria.min.js"></script>
  <script type="text/javascript" src="../node_modules/angular-material/angular-material.min.js"></script>
  <script src="//code.angularjs.org/1.8.2/angular-sanitize.js"></script>
  <!--<script type="text/javascript" src="../vendor/angular/angular.min.js"></script>-->
  <script type="text/javascript" src="../vendor/angular/angular-route.min.js"></script>
  <script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>  
  <script type="text/javascript" src="../vendor/angular-utils-pagination/dirPagination.js"></script>
  <script type="text/javascript" src="../vendor/angular-ui-bootstrap/ui-bootstrap-tpls-2.5.0.min.js"></script>
  <script type="text/javascript" src="http://cdn.jsdelivr.net/angular.checklist-model/0.1.3/checklist-model.min.js"></script>

  <script type="text/javascript" src="../web/js/lodash.js"></script>
  <script type="text/javascript" src="../ng-app/session.file.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/app.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/routes.js?v=<?php echo $_SESSION['login_token']; ?>"></script>

  <!-- CONTROLLERS -->
  <script type="text/javascript" src="../ng-app/controllers/home/home-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud/solicitud-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/vacaciones/vacaciones-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/vacaciones/view-vacacion-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud/add-solicitud-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud/edit-solicitud-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud/view-solicitud-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/ejecucion-presupuestaria-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/documento-solicitud-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/documento-orden-pago-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/documento-orden-pago-directa-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/documento-egreso-controller.js"></script>
  <script type="text/javascript" src="../ng-app/controllers/ejecucion-presupuestaria/documento-transferencia-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/consulta-vehicular/consulta-vehicular-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/consulta-vehicular/view-consulta-vehicular-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/roles/roles-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/roles/add-rol-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/roles/edit-rol-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/usuarios/usuarios-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/adelanto-viaticos/adelanto-viaticos.controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/adelanto-viaticos/view-adelanto-viaticos.controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/liquidacion-viaticos/liquidacion-viaticos.controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/liquidacion-viaticos/view-liquidacion-viaticos.controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud-recursos/solicitud-recursos-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud-recursos/view-solicitud-recursos-aj-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud-recursos/view-solicitud-recursos-ag-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/controllers/solicitud-recursos/view-solicitud-recursos-al-controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  
  <!-- COMPONENTS -->
  <script type="text/javascript" src="../ng-app/components/general/main-menu/main-menu.component.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/components/general/header-menu/header-menu.component.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <!-- Grid Component -->
  <script type="text/javascript" src="../ng-app/components/general/grid/component.module.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/components/general/grid/grid.component.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/components/general/grid/grid.controller.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  
  <!-- SERVICES -->
  <script type="text/javascript" src="../ng-app/services/solicitud-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/solicitud-recursos-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/vacaciones-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/programa-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/proveedor-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/periodo-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/usuario-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/cuenta-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/presupuesto-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/orden-pago-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/orden-pago-directa-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/egreso-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/transferencia-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/provincia-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/canton-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/consulta-vehicular-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/main-menu-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/header-menu-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/sistema-service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>
  <script type="text/javascript" src="../ng-app/services/viaticos.service.js?v=<?php echo $_SESSION['login_token']; ?>"></script>

  </body>
</html>
