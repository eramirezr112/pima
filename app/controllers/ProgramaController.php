<?php 

require ('BaseController.php');
class ProgramaController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function all() {

		session_start();		
		$codPeriodo = $_SESSION['cod_periodo'];
		$condition = "WHERE cod_periodo = $codPeriodo";

		$sql = "SELECT * FROM frm_programas $condition ORDER BY DES_PROGRAMA ASC";
		$result = $this->execute($sql);
		$programas = $this->getArray($result);

		$defaultOpt = array("COD_PROGRAMA"=>0, "DES_PROGRAMA" => "- TODOS -");
		
		$listProgramas = array();
		$p["COD_PROGRAMA"] = $defaultOpt["COD_PROGRAMA"];
		$p["DES_PROGRAMA"] = $defaultOpt["DES_PROGRAMA"];		
		array_push($listProgramas, $p);

		foreach ($programas as $key => $value) {

			$p["COD_PROGRAMA"] = $value["COD_PROGRAMA"];
			$p["DES_PROGRAMA"] = $value["DES_PROGRAMA"];

			array_push($listProgramas, $p);
		}

		echo json_encode(array('programas'=>$listProgramas));

	}

	public function get() {

		session_start();
		//$isAdmin = $_SESSION['is_admin'];
		$isAdmin = $this->checkPermision(7);
		$codPeriodo = $_SESSION['cod_periodo'];
		$codPrograma =$_SESSION['cod_programa'];

		$condition = "WHERE cod_programa = $codPrograma and cod_periodo = $codPeriodo";
		if ($isAdmin > 0) {
			$condition = "WHERE cod_periodo = $codPeriodo ORDER BY DES_PROGRAMA ASC";
		}

		$sql = "SELECT * FROM frm_programas $condition";
		$result = $this->execute($sql);
		$programas = $this->getArray($result);
		
		echo json_encode(array('programas'=>$programas));
	}

    private function checkPermision ($option) {

    	$opt_sin_acceso = $_SESSION['opt_sin_acceso'];

        if ($opt_sin_acceso != null) {
            $opciones_sin_acceso = explode(',', $opt_sin_acceso);                
            $found = true;
            foreach ($opciones_sin_acceso as $osa) {
            	
            	if ($osa == $option) {
            		$found = false;
            		break;
            	}

            }
            return $found;
        } else {
            return true;
        }

    }

	public function add() {
		die("not implemented");	
	}
}

?>