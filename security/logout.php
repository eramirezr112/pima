<?php
	session_start();
	require ('../app/config/db.php');
	$db = new DBConfig();

	$isLogged = checkIfUserIsLoged($db, $_SESSION['login_token']);

    if ($isLogged) {
    	putInactiveSession($db, $_SESSION['login_token']);
    }

	session_destroy();

	header("Location: ../");

	function checkIfUserIsLoged($conexion, $sessionKey) {

        $query = "SELECT COUNT(*) as uLoged FROM web_log_session 
                   WHERE login_token = ? 
                    AND login_status = ?";
        
        $values = array($sessionKey, 'A');
        $stmt = $conexion->executeSecure($query, $values);
        $data = $conexion->getArray($stmt)[0];

        if ($data['uLoged'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    function putInactiveSession($conexion, $sessionToken) {
        $qSessionUpdate = "UPDATE web_log_session SET login_status = ? WHERE login_token = ?";
        $vSessionUpdate = array('I', $sessionToken);
        $stmtSession = $conexion->executeSecure($qSessionUpdate, $vSessionUpdate);
    }
?>