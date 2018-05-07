<?php 

require ('BaseController.php');
class ProveedorController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

		$sql = "SELECT * FROM frm_proveedores Order by des_proveedor ASC";
		$result = $this->execute($sql);
		$proveedores = $this->getArray($result);
		
		echo json_encode(array('proveedores'=>$proveedores));
	}

	public function add() {
		die("not implemented");	
	}
}

?>