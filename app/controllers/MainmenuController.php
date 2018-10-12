<?php 

require ('BaseController.php');
class MainmenuController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

	}

	public function getOptionsByUser() {
    session_start();    
    $rolUser = $_SESSION["rol_web"];
    $connectionType = $_SESSION["CONNECTION_TYPE"];  
    
    // Modulos y Opciones sin acceso del usuario
    $sql = "SELECT p.acceso_modulo as modulos, 
                   p.opciones_sin_acceso 
          FROM web_permisos as p 
          WHERE p.rol_usuario = $rolUser";
    $result = $this->execute($sql);
    $permisos = $this->getArray($result);
    

    $filter_condition = "";
    if (!$permisos[0]['opciones_sin_acceso'] == null) {      
      $filter_condition = "AND wo.num_opcion NOT IN (".$permisos[0]['opciones_sin_acceso'].")";
    }

    // Opciones de Menu
    $sql = "SELECT wo.num_opcion, wo.cod_modulo, wo.descripcion, wo.url 
            FROM web_opciones as wo 
            WHERE wo.cod_seccion = 1 $filter_condition;";

    $result = $this->execute($sql);
    
    $allMenu = $this->getArray($result);          
    if ($connectionType == "odbc_mssql") {
      $allMenu = $this->toUtf8($allMenu);
    }
    
    echo json_encode(array('menu_options'=>$allMenu), JSON_UNESCAPED_UNICODE);
	}

	public function add() {
		die("not implemented");	
	}
}

?>