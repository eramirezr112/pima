<?php 

require ('BaseController.php');
class CantonController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

		$sql = "SELECT cod_canton as cod, des_nombre_c as nombre, cod_provincia_c as cod_provincia 
				FROM dis_cantones 
				ORDER BY des_nombre_c ASC";
		$result = $this->execute($sql);
		$cantones = $this->getArray($result);

		echo json_encode($cantones);

	}

	public function get() {
		die("not implemented");	
	}

	public function add() {
		die("not implemented");	
	}
}

?>