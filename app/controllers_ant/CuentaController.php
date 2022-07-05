<?php 

require ('BaseController.php');
class CuentaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {
		session_start();
		$codPeriodo = $_SESSION['cod_periodo'];
		$codPrograma =$_SESSION['cod_programa'];

		$sql = "SELECT ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
				FROM frm_ejecucion_presupuesto_encabe as ep, frm_cuentas as c 
				WHERE ep.cod_periodo = $codPeriodo 
					AND ep.cod_programa = $codPrograma 
					AND ep.cod_cuenta = c.cod_cuenta 
					ORDER BY c.des_cuenta ASC";
		$result = $this->execute($sql);
		$cuentas = $this->getArray($result);

		$listCuentas = array();
		foreach ($cuentas as $key => $value) {

			$c["key"]            = $key;
			$c["cod_cuenta"]     = $value["cod_cuenta"];
			$c["des_cuenta"]     = $value["des_cuenta"];
			$c["mon_disponible"] = $value["mon_disponible"];
			$c["num_cuenta"]     = $value["num_cuenta"];

			array_push($listCuentas, $c);
		}

		echo json_encode(array('cuentas'=>$listCuentas));
	}

	public function get() {
		session_start();
		$params = $this->getParameters();
		$vCodPrograma = $params["codPrograma"];
		
		$codPeriodo = $_SESSION['cod_periodo'];
		$codPrograma = $vCodPrograma;

		$sql = "SELECT ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
				FROM frm_ejecucion_presupuesto_encabe as ep, frm_cuentas as c 
				WHERE ep.cod_periodo = $codPeriodo 
					AND ep.cod_programa = $codPrograma 
					AND ep.cod_cuenta = c.cod_cuenta";
		$result = $this->execute($sql);
		$cuentas = $this->getArray($result);

		$defaultOpt = array("mon_disponible"=>".00", "num_cuenta"=> 0,  "des_cuenta" => "- SELECCIONE UNA CUENTA -", "cod_cuenta" => 0);
		array_push($cuentas, $defaultOpt);

		$listCuentas = array();
		foreach ($cuentas as $key => $value) {

			$c["key"]            = $key;
			$c["cod_cuenta"]     = $value["cod_cuenta"];
			$c["des_cuenta"]     = $value["des_cuenta"];
			$c["mon_disponible"] = $value["mon_disponible"];
			$c["num_cuenta"]     = $value["num_cuenta"];

			array_push($listCuentas, $c);
		}

		echo json_encode(array('cuentas'=>$listCuentas));
	}

	public function add() {
		die("not implemented");	
	}
}

?>