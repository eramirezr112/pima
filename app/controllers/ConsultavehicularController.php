<?php 

require ('BaseController.php');
class ConsultavehicularController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

		$sql = "SELECT vde.cod_provincia, p.des_nombre_p as provincia, vde.cod_canton, c.des_nombre_c as canton, 
				vde.cod_periodo, vde.num_cedula, vde.fec_registro, vde.des_usuario, 
				c.des_nombre_c, vde.des_PLACA,
         		(SELECT CAM_PADRON.des_apell1 + ' ' + CAM_PADRON.des_apell2 + ' ' + CAM_PADRON.des_nombre 
         		 FROM  CAM_PADRON   
         		 WHERE CAM_PADRON.num_cedula = vde.num_cedula) as des_nombre, 
         		vde.ind_extrangero, 
         		vde.num_extrangero, 
         		vde.des_extrangero, 
         		vde.des_nombre_jur,
         		vde.ind_persona,
         		vde.ind_estado 
    			FROM FRM_VEHICULOS_DIAE as vde, 
    				 DIS_cantones as c, 
    				 dis_provincias as p 
   				WHERE ( vde.cod_provincia = c.cod_provincia_c ) 
   					AND ( vde.cod_canton = c.cod_canton ) 
   					AND ( vde.cod_provincia = p.cod_provincia) 
   					AND ( c.cod_provincia_c = p.cod_provincia)
   					AND ( vde.cod_provincia != 8 ) 
            AND ( vde.cod_periodo = 14 )";
   					
		$result = $this->execute($sql);
		$consulta = $this->getArray($result);

		echo json_encode($consulta);

	}

	public function get() {
    session_start();
    $params = $this->getParameters();
    $codConsulta =$params["idConsulta"];  

    $sql = "SELECT vde.*, p.des_nombre_p as provincia, vde.cod_canton, c.des_nombre_c as canton, 
        vde.cod_periodo, vde.num_cedula, vde.fec_registro, vde.des_usuario, 
        c.des_nombre_c, vde.des_PLACA,
            (SELECT CAM_PADRON.des_apell1 + ' ' + CAM_PADRON.des_apell2 + ' ' + CAM_PADRON.des_nombre 
             FROM  CAM_PADRON   
             WHERE CAM_PADRON.num_cedula = vde.num_cedula) as des_nombre, 
            vde.ind_extrangero, 
            vde.num_extrangero, 
            vde.des_extrangero, 
            vde.des_nombre_jur,
            vde.ind_persona,
            vde.ind_estado 
          FROM FRM_VEHICULOS_DIAE as vde, 
             DIS_cantones as c, 
             dis_provincias as p 
          WHERE ( vde.cod_provincia = c.cod_provincia_c ) 
            AND ( vde.cod_canton = c.cod_canton ) 
            AND ( vde.cod_provincia = p.cod_provincia) 
            AND ( c.cod_provincia_c = p.cod_provincia)
            AND ( vde.cod_provincia != 8)
            AND ( vde.num_cedula = '$codConsulta')
            AND ( vde.cod_periodo = 14 )";
    $result = $this->execute($sql);
    $consultaVehicular = $this->getArray($result);            

    $allLines = array();    
    echo json_encode(array('consulta'=>$consultaVehicular, 'detalle'=> $allLines));
	}

	public function add() {
		die("not implemented");	
	}
}

?>