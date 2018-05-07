<?php 

	require ('../app/config/db.php');

	$user     = trim($_POST['user']);
	$pass     = trim($_POST['pass']);
	$realPass = getLoginPassword($pass);

	$db = new DBConfig();

	$jData = getJoinData($user);
	$join_table         = $jData['join_table'];
	$fields_funcionario = $jData['fields_funcionario'];
	$join_condition     = $jData['join_condition'];

	$query = "SELECT u.cod_usuario, 
				u.des_usuario, 
				u.des_login, 
				u.ind_estado, 
				u.cod_perfil, 
				u.ind_bloqueo,
				u.rol_web 
				$fields_funcionario
			  FROM seg_usuarios as u $join_table
			  WHERE des_login = ? 
			  	AND des_password = ? 
			  	$join_condition";

	$values = array($user, $realPass);
	$stmt = $db->executeSecure($query, $values);	
	$data = $db->getArray($stmt);

	if (is_array($data) && sizeof($data) > 0) {
		session_start();
		$user = $data[0];

		$is_admin_login = false;
		if (sizeof($user) <= 7){
			$is_admin_login = true;
		}
			
        if ($user['rol_web'] != null) {

			$query = "SELECT *  
		              FROM web_permisos as wp 
					  WHERE wp.rol_usuario = ?";
			
			$values = array($user['rol_web']);
			$stmt = $db->executeSecure($query, $values); 
			$permits = $db->getArray($stmt);

			$_SESSION['cod_usuario']    = $user["cod_usuario"];
			$_SESSION['des_usuario']    = $user["des_usuario"];
			$_SESSION['des_login']      = $user["des_login"];	
			$_SESSION['ind_estado']     = $user["ind_estado"];
			$_SESSION['ind_bloqueo']    = $user["ind_bloqueo"];
			$_SESSION['rol_web']        = $user['rol_web'];
			$_SESSION['modulos_acceso'] = $permits[0]['acceso_modulo'];
			$_SESSION['opt_sin_acceso'] = $permits[0]['opciones_sin_acceso'];

			if ($is_admin_login == false) {				
				$funcionario = array();
	            $funcionario['COD_FUNCIONARIO']         = $user["COD_FUNCIONARIO"];
	            $funcionario['DES_CEDULA']              = $user["DES_CEDULA"];
	            $funcionario['DES_APELLIDO1']           = $user["DES_APELLIDO1"];
	            $funcionario['DES_APELLIDO2']           = $user["DES_APELLIDO2"];
	            $funcionario['DES_NOMBRE']              = $user["DES_NOMBRE"];
	            $funcionario['COD_CENTRO']              = $user["COD_CENTRO"];
	            $funcionario['IND_TIPO_IDENTIFICACION'] = $user["IND_TIPO_IDENTIFICACION"];
	            $funcionario['IND_ESTADO_FUNC']         = $user["IND_ESTADO"];

				foreach ($funcionario as $key => $value) {
					$_SESSION[$key] = $value;
				}


				// Obtencion de los Centros Costo
				// ======================================
				$query = "SELECT cc.cod_centro 
						  FROM rh_centros_costo as cc
						  WHERE cc.cod_encargado = ?";
				
				$values = array($user["COD_FUNCIONARIO"]);
				$stmt = $db->executeSecure($query, $values); 
				$centros_costo = $db->getArray($stmt);

				$allCentrosCosto = [];
				foreach ($centros_costo as $centro) {
					array_push($allCentrosCosto, $centro['cod_centro']);
				}

				$query = "SELECT ad.cod_centro 
						  FROM rh_autorizados_direccion as ad
						  WHERE ad.cod_funcionario = ?";
				
				$values = array($user["COD_FUNCIONARIO"]);
				$stmt = $db->executeSecure($query, $values); 
				$centros_costo_autorizados_direccion = $db->getArray($stmt);

				foreach ($centros_costo_autorizados_direccion as $centro) {
					array_push($allCentrosCosto, $centro['cod_centro']);
				}

				// Se colocan los centros de costo en session.
				$_SESSION['CENTROS_COSTO'] = $allCentrosCosto;

			}

			$key = getUniqueLoginAccess();
			$_SESSION['login_token']  = $key;

			header("Location: ../app");
        } else {

			session_destroy();
			echo "<script>";
			echo "alert('Este usuario No tiene permisos asignados. Favor comuniquese con el Administrador');";
			echo "window.location = '../';";
			echo "</script>";
			exit;
        }

	} else {
		header("Location: ../");
	}

	/**
	 * Get the truly password access for the user
	 * @param $pass password provided by th web form
	 */
	function getLoginPassword($pass) {
		$passArray = str_split($pass);

		$passVal = 0;
		$listVals = array();
		for ($i=0; $i < sizeof($passArray); $i++) { 
			$passVal = $passVal + (ord($passArray[$i]) * ($i+1));
			array_push($listVals, $passVal);		
		}

		$expo2 = pow($passVal, 2) * 8;	
		if ($expo2 > 2147483647) {
			$expo2 = 0;
		}

		$expo9 = pow($passVal, 9) * 8;
		if ($expo9 > 2147483647) {
			$expo9 = 0;
		}

		$realPass = $expo2 + $expo9;

		return $realPass;
	}

	/**
	 *
	 */
	function getUniqueLoginAccess () {
		$key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
		return $key;
	}

	function getJoinData($user){
		$join_table         = "";
		$fields_funcionario = "";
		$join_condition     = "";

		if ($user != "admin") {
			$join_table = ", rh_funcionarios as f ";
			$fields_funcionario = ", f.COD_FUNCIONARIO, 
				f.DES_CEDULA, 
				f.DES_APELLIDO1, 
				f.DES_APELLIDO2, 
				f.DES_NOMBRE, 
				f.COD_CENTRO, 
				f.IND_TIPO_IDENTIFICACION, 
				f.IND_ESTADO";
			$join_condition = "AND u.cod_funcionario = f.cod_funcionario";
		}

		return array (
			"join_table"         => $join_table,
			"fields_funcionario" => $fields_funcionario,
			"join_condition"     => $join_condition,
		);
	}

?>