<?php 


require ('BaseController.php');
class UsuarioController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {
		session_start();
		$current_cod_user = $_SESSION['cod_usuario'];

		$sql = "SELECT u.cod_usuario as codigo, 
					   u.des_usuario as nombre, 
					   u.rol_web as rol, 
					   u.ind_estado as estado, 
					   'false' as isChange
				FROM seg_usuarios as u 
				WHERE u.cod_usuario != $current_cod_user 
				ORDER BY u.ind_estado, u.des_usuario ASC";
		$result = $this->execute($sql);
		$usuarios = $this->getArray($result);

		$listUsuarios = array();
		foreach ($usuarios as $key => $u) {			
			$rolWeb = $u['rol'];
			$rolInfo = array();
			if ($rolWeb != null) {
				$sql = "SELECT r.rol_usuario, r.descripcion FROM web_roles as r WHERE r.rol_usuario = $rolWeb";
				$result = $this->execute($sql);
				$data = $this->getArray($result);
				if($data) {
					$rolInfo['rol_usuario'] = $data[0]['rol_usuario'];				
					$rolInfo['descripcion'] = $data[0]['descripcion'];				
				}
	
			}

			$userData["codigo"] = $u['codigo'];
			$userData["nombre"] = $u['nombre'];
			$userData["rol"]    = $rolInfo;
			$userData["estado"] = $u['estado'];
			$userData["isChange"] = $u['isChange'];

			array_push($listUsuarios, $userData);

		}

		echo json_encode($listUsuarios);
	}

	public function get() {
		session_start();
		$connectionType = $_SESSION["CONNECTION_TYPE"];
		
		$userData = array();
		foreach ($_SESSION as $key => $value) {
			if ($key !== 'IMG_FOTO'){
				$userData[$key] = $value;
			}
		}

	    if ($connectionType == "odbc_mssql") {
	    	echo json_encode(array('usuario'=> $this->sessionToUtf8($userData)), JSON_UNESCAPED_UNICODE);
	    } else {	    	
	    	echo json_encode(array('usuario'=> $userData));	
	    }
		
	}

	public function getPermisionByUser() {
		$params = $this->getParameters();
		$codUser =$params["codUser"];


	    $sql = "SELECT p.rol_usuario as rol, p.acceso_modulo as modulos, 
	                   p.opciones_sin_acceso 
	          FROM web_permisos as p 
	          WHERE p.cod_usuario = $codUser ";
	    $result = $this->execute($sql);
	    $infoUser = $this->getArray($result);

	    if (sizeof($infoUser) > 0) {
	    	$rol = $infoUser[0]["rol"];

	    	if ($rol > 1) {
			    $pModulos = explode(',', $infoUser[0]["modulos"]);

			    $permisos = array();
			    foreach ($pModulos as $key => $modulo) {
			        
			        $theModule = intval(trim($modulo));
			        $permisos[$key]['codigo_modulo'] = $theModule;		        

			        //get opciones by user
			        $sql = "SELECT wo.num_opcion, wo.descripcion 
			                FROM web_opciones as wo 
			                WHERE wo.cod_modulo = $modulo 
			                AND wo.num_opcion NOT IN (".$infoUser[0]['opciones_sin_acceso'].")";
			        $result = $this->execute($sql);
			        $opciones = $this->getArray($result);

			        //get total opciones by modulo
					$totalOptions = $this->countOptionByModule($theModule);

					$permisos[$key]['checkAll'] = false;
					if ($totalOptions == sizeof($opciones)) {
						$permisos[$key]['checkAll'] = true;
					}

			        //get nombre del modulo
			        $sqlM = "SELECT wm.descripcion 
			                FROM web_modulos as wm 
			                WHERE wm.cod_modulo = $modulo";
			        $resultM = $this->execute($sqlM);
			        $nModulo = $this->getArray($resultM);
			        
			        $permisos[$key]['nombre_modulo']   = $nModulo[0]["descripcion"];
			        $permisos[$key]['opciones_modulo'] = $opciones;
			    }
		    } else {
	  			$permisos = [];
		    }

    	} else {
  			$permisos = null;
  			$rol = null;
    	}
    	
	    echo json_encode(array("rol"=>$rol, "permisos"=>$permisos));

	}

	public function savePermits() {
		$params = $this->getParameters();
		
		$rolData = json_decode($params["rolData"]);
		//$codUser = $rolData->codUser;
		$rolDescription = $rolData->description;
		$permits = $rolData->permits;

		// Get List Modulos
		$strModulos = "";
		$listModulos = array();
		foreach ($permits as $key => $p) {
			if (sizeof($p->opciones_modulo) > 0 ) {

				$strModulos .= intval($p->codigo_modulo).",";
				$modulo["codModulo"] = intval($p->codigo_modulo);
				$modulo["opciones"] = $p->opciones_modulo;

				array_push($listModulos, $modulo);

			} else {
				if ($p->checkAll) {
					$strModulos .= intval($p->codigo_modulo).",";
					$modulo["codModulo"] = intval($p->codigo_modulo);
					$modulo["opciones"] = $p->opciones_modulo;
					
					array_push($listModulos, $modulo);
				}
			}
		}

		$modulos =  substr($strModulos, 0, -1);

		$strWithOutAccess = "";
		$counter = 1;
		foreach ($listModulos as $key => $m) {

			if (sizeof($m["opciones"]) > 0) {

				$strOpciones = "";
				foreach ($m["opciones"] as $key => $o) {
					$xOpcion = intval($o->num_opcion);
					$strOpciones .= $xOpcion.",";
				}

				$opciones =  substr($strOpciones, 0, -1);

		        $sql = "SELECT wo.num_opcion 
		                FROM web_opciones as wo 
		                WHERE wo.cod_modulo = ".$m["codModulo"]." 
		                AND wo.num_opcion NOT IN ($opciones)";
		        $result = $this->execute($sql);
		        $o_without_access = $this->getArray($result);

		        //adding options without access
		        foreach ($o_without_access as $key => $wa) {
		        	$strWithOutAccess .= intval($wa["num_opcion"]).",";
		        }

			}
		        $counter++;
		}

		$optWithoutAccess =  substr($strWithOutAccess, 0, -1);

		if ($optWithoutAccess == false) {
			$noAccess = 'NULL';
		} else {
			$noAccess = $optWithoutAccess;
		}

        $sql = "INSERT INTO web_roles (descripcion) 
        		VALUES ('$rolDescription')";
        $result = $this->execute($sql);
        $last = $this->conn->Insert_ID();
        $lastIdRole = intval($last);

        $values = "($lastIdRole, '$modulos', '$noAccess')";
        if ($noAccess == 'NULL') {
        	$values = "($lastIdRole, '$modulos', null)";
        }

        $sql = "INSERT INTO web_permisos (rol_usuario, acceso_modulo, opciones_sin_acceso) 
        		VALUES $values";
        $result = $this->execute($sql);
        $last = $this->conn->Insert_ID();
        $lastIdPermisos = intval($last);

        if($lastIdPermisos > 0) {
        	echo json_encode(1);
        } else {
        	echo json_encode(0);
        }

	}

	public function updateRol() {
		$params = $this->getParameters();
		
		$rolData = json_decode($params["rolData"]);
		//$codUser = $rolData->codUser;
		$rolId = $rolData->idRol;
		$rolDescription = $rolData->description;
		$permits = $rolData->permits;

		// Get List Modulos
		$strModulos = "";
		$listModulos = array();
		foreach ($permits as $key => $p) {
			if (sizeof($p->opciones_modulo) > 0 ) {

				$strModulos .= intval($p->codigo_modulo).",";
				$modulo["codModulo"] = intval($p->codigo_modulo);
				$modulo["opciones"] = $p->opciones_modulo;

				array_push($listModulos, $modulo);

			} else {
				if ($p->checkAll) {
					$strModulos .= intval($p->codigo_modulo).",";
					$modulo["codModulo"] = intval($p->codigo_modulo);
					$modulo["opciones"] = $p->opciones_modulo;
					
					array_push($listModulos, $modulo);
				}
			}
		}

		$modulos =  substr($strModulos, 0, -1);

		$strWithOutAccess = "";
		$counter = 1;
		foreach ($listModulos as $key => $m) {

			if (sizeof($m["opciones"]) > 0) {

				$strOpciones = "";
				foreach ($m["opciones"] as $key => $o) {
					$xOpcion = intval($o->num_opcion);
					$strOpciones .= $xOpcion.",";
				}

				$opciones =  substr($strOpciones, 0, -1);

		        $sql = "SELECT wo.num_opcion 
		                FROM web_opciones as wo 
		                WHERE wo.cod_modulo = ".$m["codModulo"]." 
		                AND wo.num_opcion NOT IN ($opciones)";
		        $result = $this->execute($sql);
		        $o_without_access = $this->getArray($result);

		        //adding options without access
		        foreach ($o_without_access as $key => $wa) {
		        	$strWithOutAccess .= intval($wa["num_opcion"]).",";
		        }

			}
		        $counter++;
		}

		$optWithoutAccess =  substr($strWithOutAccess, 0, -1);

		if ($optWithoutAccess == false) {
			$noAccess = 'NULL';
		} else {
			$noAccess = $optWithoutAccess;
		}

		// Actualiza el nombre del Rol
        $sql = "UPDATE web_roles set descripcion = '$rolDescription'  
        		WHERE rol_usuario = $rolId";
        $result = $this->execute($sql);

		// Actualiza los permisos del rol
        $sql = "UPDATE web_permisos set acceso_modulo = '$modulos', opciones_sin_acceso = '$noAccess' 
        		WHERE rol_usuario = $rolId";
        $result = $this->execute($sql);        

        if($result) {
        	echo json_encode(1);
        } else {
        	echo json_encode(0);
        }

	}

	private function countOptionByModule($modulo) {
        $sql = "SELECT count(wo.num_opcion) as total 
                FROM web_opciones as wo 
                WHERE wo.cod_modulo = $modulo";
        $result = $this->execute($sql);
        $data = $this->getArray($result);

        return $data[0]["total"];
	}

	public function updateUserRol() {

		$params = $this->getParameters();
		
		$userData = json_decode($params["userData"]);
		$codUser = intval($userData->codUser);
		$rolUser = intval($userData->rolUser);

        $sql = "UPDATE seg_usuarios set rol_web = $rolUser WHERE cod_usuario = $codUser";
        $result = $this->execute($sql);

        if ($result) {
        	echo json_encode(1);
        } else {
        	echo json_encode(0);
        }

	}

	public function deleteRol(){
		$params = $this->getParameters();		
		$codRol = json_decode($params["codRol"]);
		
        $sql = "DELETE from web_roles WHERE rol_usuario = $codRol";
        $result = $this->execute($sql);

        $sql = "DELETE from web_permisos WHERE rol_usuario = $codRol";
        $result = $this->execute($sql);

        if ($result) {
        	echo json_encode(1);
        } else {
        	echo json_encode(0);
        }
        
	}

	public function add() {
		die("not implemented");	
	}
}

?>