<?php 

require ('BaseController.php');
class TransferenciaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function get() {
		
		session_start();
		$params = $this->getParameters();
		$numPlanilla =$params["numPlanilla"];	
		
		$sql = "SELECT op.*, u.des_usuario as usuario, b.num_cuenta as cuenta, u2.des_usuario as uGenera, u3.des_usuario as uAplica 
				FROM frm_transferencias_enca as op, 
				     seg_usuarios as u, frm_bancos as b, seg_usuarios as u2, seg_usuarios as u3 
				WHERE op.NUM_DOCUMENTO = $numPlanilla 
				AND op.cod_usuario = u.cod_usuario 
				AND op.cod_cuenta = b.cod_cuenta 
				AND op.cod_usuario = u2.cod_usuario 
				AND op.cod_usuario = u3.cod_usuario";

		$result = $this->execute($sql);
		$transferencia = $this->getArray($result);

		$idPlanilla = $transferencia[0]['NUM_PLANILLA'];		
		$sqlDetalle = "SELECT d.*, p.des_proveedor as proveedor, p.num_cedula as cedula   
		               FROM frm_transferencias_deta as d, frm_proveedores as p 
		               WHERE d.num_planilla = $idPlanilla 
		               	AND d.cod_proveedor = p.cod_proveedor";

		$resultDetalle = $this->execute($sqlDetalle);
		$allLines = $this->getArray($resultDetalle);
		
		echo json_encode(array('transferencia'=>$transferencia, 'detalle'=> $allLines));
	}

}

?>