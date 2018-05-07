<?php 

require ('BaseController.php');
class EgresoController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function get() {
		
		session_start();
		$params = $this->getParameters();
		$codEgreso =$params["idEgreso"];	

		
		$sql = "SELECT op.*, u.des_usuario as usuario, pe.num_year as periodo 
				FROM frm_otros_documentos_encabezado as op, 
				     seg_usuarios as u,
				     cfg_cam_periodos_electorales as pe 
				WHERE op.num_documento = $codEgreso 
				AND op.cod_usuario = u.cod_usuario 
				AND op.cod_periodo = pe.cod_periodo";		

		$result = $this->execute($sql);
		$docEgreso = $this->getArray($result);


		$codPeriodo = $docEgreso[0]['cod_periodo'];
		$sqlDetalle = "SELECT d.*, c.des_cuenta as cuenta, p.des_programa as programa  
		               FROM frm_otros_documentos_detalle as d, frm_cuentas as c, frm_programas as p 
		               WHERE d.num_documento = $codEgreso 
		               	AND d.cod_cuenta = c.cod_cuenta 
		               	AND d.cod_programa = p.cod_programa
		               	AND p.cod_periodo = $codPeriodo";

		$resultDetalle = $this->execute($sqlDetalle);
		$allLines = $this->getArray($resultDetalle);

		echo json_encode(array('egreso'=>$docEgreso, 'detalle'=> $allLines));
	}

}

?>