<?php 

require ('BaseController.php');
class SistemaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function getRoles() {

        $sql = "SELECT * FROM web_roles as r";
        $result = $this->execute($sql);
        $roles = $this->getArray($result);
        
        echo json_encode($roles);
	}

    public function getPermisos() {

        $sql = "SELECT * FROM web_modulos as m";
        $result = $this->execute($sql);
        $modulos = $this->getArray($result);

        $permisos = array();
        foreach ($modulos as $key => $modulo) {
            
            $permisos[$key]['codigo_modulo'] = $modulo['cod_modulo'];
            $permisos[$key]['nombre_modulo'] = $modulo['descripcion'];

            $codModulo = $modulo['cod_modulo'];
            $sql = "SELECT wo.num_opcion, wo.descripcion 
                    FROM web_opciones as wo 
                    WHERE wo.cod_modulo = $codModulo";
            $result = $this->execute($sql);
            $opciones = $this->getArray($result);        

            $permisos[$key]['opciones_modulo'] = $opciones;
        }

        echo json_encode($permisos);

    }

    public function getInfoRol() {
        $params = $this->getParameters();
        $id = $params["idRol"];

        $sql = "SELECT r.rol_usuario, r.descripcion, p.acceso_modulo, p.opciones_sin_acceso  
                FROM web_roles as r, web_permisos as p 
                WHERE r.rol_usuario = $id 
                AND r.rol_usuario = p.rol_usuario";
        $result = $this->execute($sql);
        $rolInfo = $this->getArray($result);
        
        $rModulos  = $rolInfo[0]['acceso_modulo'];
        $rPermisos = $rolInfo[0]['opciones_sin_acceso'];

        $pModulos  = explode(',', $rModulos);
        $pPermisos = explode(',', $rPermisos);


        $permisos = array();
        foreach ($pModulos as $key => $modulo) {
            
            $theModule = intval(trim($modulo));
            $permisos[$key]['codigo_modulo'] = $theModule;              

            //get opciones by user
            $sql = "SELECT wo.num_opcion, wo.descripcion 
                    FROM web_opciones as wo 
                    WHERE wo.cod_modulo = $modulo 
                    AND wo.num_opcion NOT IN (".$rPermisos.")";
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


        echo json_encode(array("rol"=>$id, "descripcion"=>$rolInfo[0]['descripcion'], "permisos"=>$permisos));

    }

    private function countOptionByModule($modulo) {
        $sql = "SELECT count(wo.num_opcion) as total 
                FROM web_opciones as wo 
                WHERE wo.cod_modulo = $modulo";
        $result = $this->execute($sql);
        $data = $this->getArray($result);

        return $data[0]["total"];
    }

	public function add() {
		die("not implemented");	
	}
}

?>