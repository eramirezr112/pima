<?php 

require ('BaseController.php');
class OrdenpagodirectaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function get() {
		
		session_start();
		$params = $this->getParameters();
		$codOrden =$params["idOrden"];	

		$sql = "SELECT op.*, u.des_usuario as usuario, p.des_proveedor, pe.num_year as periodo, pr.des_programa as programa 
				FROM frm_orden_pago_dir as op, 
				     seg_usuarios as u, 
				     frm_proveedores as p, 
				     cfg_cam_periodos_electorales as pe,
				     frm_programas as pr 
				WHERE op.cod_orden = $codOrden 
				AND op.cod_usuario = u.cod_usuario 
				AND op.cod_proveedor = p.cod_proveedor 
				AND op.cod_periodo = pe.cod_periodo 
				AND op.cod_programa = pr.cod_programa";		

		$result = $this->execute($sql);
		$ordenPago = $this->getArray($result);

		$sqlDetalle = "SELECT d.*, c.des_cuenta 
		               FROM frm_orden_pago_dir_deta as d, frm_cuentas as c 
		               WHERE d.cod_orden = $codOrden 
		               	AND d.cod_cuenta = c.cod_cuenta";

		$resultDetalle = $this->execute($sqlDetalle);
		$allLines = $this->getArray($resultDetalle);		
		

		//echo json_encode(array('orden'=>$ordenPago, 'detalle'=> $allLines));
		echo json_encode(array('orden'=>$ordenPago, 'detalle'=> $allLines));
	}

}

?>