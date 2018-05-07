<?php 

require ('BaseController.php');
class PeriodoController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {
		
		session_start();		
		$codPeriodo = $_SESSION['cod_periodo'];

		$sql = "SELECT cod_periodo, num_year FROM cfg_cam_periodos_electorales WHERE cod_periodo >= $codPeriodo";
		$result = $this->execute($sql);
		$periodos = $this->getArray($result);
		
		echo json_encode(array('periodos'=>$periodos));
	}

	public function get() {
		
		session_start();		
		$codPeriodo = $_SESSION['cod_periodo'];

		$sql = "SELECT cod_periodo, num_year FROM cfg_cam_periodos_electorales WHERE cod_periodo = $codPeriodo";
		$result = $this->execute($sql);
		$periodo = $this->getArray($result);
		
		echo json_encode(array('periodo'=>$periodo));
	}

	public function add() {
		die("not implemented");	
	}
}

?>