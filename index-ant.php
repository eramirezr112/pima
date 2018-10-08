<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PIMA | Autorizaciones</title>
    <link rel="icon" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-32x32.png" sizes="32x32">
    <link rel="icon" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-192x192.png" sizes="192x192">
    <link rel="apple-touch-icon-precomposed" href="http://www.pima.go.cr/wp-content/uploads/2017/05/cropped-logo-180x180.png">
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="web/css/login.css">
</head>
<body>

    <div class="container">
        <div class="card-container card">
        <div class="row">
            <div class="col-md-12">
                <img id="pima" class="img-responsive" src="web/img/logo-default.jpg" style="margin:0 auto; width:150px;" />                
                <form class="form-signin" action="security/login.php" method="post">
                    <span id="reauth-email" class="reauth-email"></span>
                    <input type="text" name="user" value="" id="user" class="form-control input-sm" placeholder="Usuario" required autofocus>
                    <input type="password" name="pass" value="" id="pass" class="form-control input-sm" placeholder="Contraseña" required>                
                    <button class="btn btn-warning btn-block  btn-xs" type="submit">Ingresar</button>
                </form><!-- /form -->
                <br>
            </div>
        </div>
        </div>
    </div><!-- /container -->

</body>
</html>