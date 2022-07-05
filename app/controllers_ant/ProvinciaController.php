<?php 

require ('BaseController.php');
class ProvinciaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

		$sql = "SELECT cod_provincia as cod, des_nombre_p as nombre 
				FROM dis_provincias 
				WHERE cod_provincia != 8 
				ORDER BY des_nombre_p ASC";
		$result = $this->execute($sql);
		$provincias = $this->getArray($result);

		/*
		$provincias = array();
		$default = ['cod' => 0, 'nombre'=>"TODAS"];
		array_push($provincias, $default);
		
		foreach ($allProvincias as $p) {
			array_push($provincias, $p);
		}
		*/

		echo json_encode($provincias);

	}

	public function get() {
		die("not implemented");	
	}

	public function add() {
		die("not implemented");	
	}
}

?>