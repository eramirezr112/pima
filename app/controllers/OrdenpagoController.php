<?php 

require ('BaseController.php');
class OrdenpagoController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function get() {
		
		session_start();
		$params = $this->getParameters();
		$codOrden =$params["idOrden"];	

		$sql = "SELECT op.*, u.des_usuario as usuario, p.des_proveedor, pe.num_year as periodo 
				FROM frm_orden_pago as op, 
				     seg_usuarios as u, 
				     frm_proveedores as p, 
				     cfg_cam_periodos_electorales as pe 
				WHERE op.cod_orden = $codOrden 
				AND op.cod_usuario = u.cod_usuario 
				AND op.cod_proveedor = p.cod_proveedor 
				AND op.cod_periodo = pe.cod_periodo";		

		$result = $this->execute($sql);
		$ordenPago = $this->getArray($result);
		
		$sqlDetalle = "SELECT d.*, f.num_factura, c.des_cuenta 
		               FROM frm_orden_pago_deta as d, frm_factura_enca as f, 
		                    frm_cuentas as c 
		               WHERE d.cod_orden = $codOrden 
		               	AND d.cod_factura = f.cod_factura 
		               	AND d.cod_cuenta = c.cod_cuenta";		

		$resultDetalle = $this->execute($sqlDetalle);
		$allLines = $this->getArray($resultDetalle);		

		echo json_encode(array('orden'=>$ordenPago, 'detalle'=> $allLines));
	}

}

?>