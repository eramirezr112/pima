<?php 

require ('BaseController.php');
class PresupuestoController extends BaseController
{

	function __construct($c, $f) {
		parent::__construct($c, $f);
	}

	public function getEncabezado() {
		session_start();
		$codPeriodo  = $_SESSION['cod_periodo'];

		$params = $this->getParameters();
		$codPrograma = $params["codPrograma"];
		$codPartida  = $params["codPartida"];
		$codEstado   = $params["codEstado"];

		/*
		$conditions = "AND cod_programa = $codPrograma 
			AND cod_cuenta = $codCuenta;"
		*/

		$sql = "SELECT p.num_year as periodo, 
					pe.cod_programa, 
					pr.des_programa as programa, 
					c.num_cuenta as partida, 
					pe.cod_cuenta, 
					c.des_cuenta as descripcion, 
					pe.mon_ordinario as presupuesto_ordinario, 
					pe.mon_modificaciones as modificaciones, 
					pe.mon_presupuesto_modificado as total_presupuesto, 
					pe.mon_compromiso_provisional as compromiso_reservado, 
					pe.mon_compromiso_definitivo as compromiso_aprobado, 
					pe.mon_disponible as disponible, 
					pe.mon_gasto_real as ejecutado 
				FROM frm_ejecucion_presupuesto_encabe AS pe, 
					 cfg_cam_periodos_electorales as p, 
					 frm_programas as pr, 
					 frm_cuentas as c
				WHERE pe.cod_periodo = $codPeriodo 
					AND pe.cod_periodo = p.cod_periodo 
					AND pe.cod_programa = pr.cod_programa 
					AND pe.cod_periodo = pr.cod_periodo 
					AND pe.cod_cuenta = c.cod_cuenta 					
					AND pe.ind_estado = $codEstado 
					ORDER BY pr.des_programa, c.num_cuenta ASC";	
		$result = $this->execute($sql);
		$encabezado = $this->getArray($result);
		
		$totales = array('tot_presupuesto_ordinario' => 0, 
					     'tot_modificaciones'        => 0, 
				         'tot_total_presupuesto'     => 0, 
				         'tot_compromiso_reservado'  => 0, 
				         'tot_compromiso_aprobado'   => 0, 
				         'tot_disponible'            => 0, 
				         'tot_ejecutado'             => 0
					);

		// First line of detalle
		$firstLine    = 0;		
		$fCodPrograma = 0;
		$fCodCuenta   = 0;
		foreach ($encabezado as $k => $line) {
			if ($firstLine == 0) {
				$fCodPrograma = $line['cod_programa'];
				$fCodCuenta   = $line['cod_cuenta'];
			}
			$totales['tot_presupuesto_ordinario'] += $line['presupuesto_ordinario'];
			$totales['tot_modificaciones'] += $line['modificaciones'];
			$totales['tot_total_presupuesto'] += $line['total_presupuesto'];
			$totales['tot_compromiso_reservado'] += $line['compromiso_reservado'];
			$totales['tot_compromiso_aprobado'] += $line['compromiso_aprobado'];
			$totales['tot_disponible'] += $line['disponible'];
			$totales['tot_ejecutado'] += $line['ejecutado'];

			$firstLine++;
		}

		$initDetalle = $this->getDetalle($fCodPrograma, $fCodCuenta);
		echo json_encode(array('encabezado' => $encabezado, 'totales' => $totales, 'registros' => sizeof($encabezado), 'initDetalle' => $initDetalle));
	}

	public function getDetalle($cP = null, $cC = null) {
		
		if ($cP != null && $cC != null) {
			$codPeriodo  = $_SESSION['cod_periodo'];			
			$codPrograma = $cP;
			$codCuenta   = $cC;
		} else {
			session_start();
			$codPeriodo  = $_SESSION['cod_periodo'];			
			$params = $this->getParameters();
			$codPrograma = $params["codPrograma"];
			$codCuenta   = $params["codCuenta"];
		}

		// Tabs Reservado, Aprobado, Ejecutado
		$reservado      = array ();
		$aprobado       = array ();
		$ejecutado      = array ();

		$sql = "SELECT * 
				FROM frm_ejecucion_presupuesto_detall AS epd 
				WHERE epd.cod_periodo = $codPeriodo 
					AND epd.cod_programa = $codPrograma 
					AND epd.cod_cuenta = $codCuenta";

		$result = $this->execute($sql);
		$allDetalle = $this->getArray($result);

		foreach ($allDetalle as $k => $line) {

			$line['tip_doc'] = intval($line['tip_documento']);
			$line['tip_documento'] = $this->getTipoDocumento($line['tip_documento']);			
			
			switch ($line['tip_detalle']) {
				case 'CP':
					array_push($reservado, $line);
					break;
				case 'CD':
					array_push($aprobado, $line);
					break;
				case 'GR':
					array_push($ejecutado, $line);
					break;
			}
		}


		//Tab Modificaciones
		$modificaciones = array ();

		$sqlM = "SELECT mE.cod_periodo, 
         			mE.num_modificacion, 
         			mE.tip_modificacion, 
         			mE.fec_modificacion, 
         			mD.cod_detalle, 
         			mD.mon_aumentar, 
         			mD.mon_disminuir, 
         			mD.mon_total 
    			FROM frm_modificaciones_detalle as mD, 
         			frm_modificaciones_encabezado as mE 
   				WHERE mE.cod_periodo = mD.cod_periodo 
   					AND mE.num_modificacion = mD.num_modificacion 
        			AND mD.cod_periodo = $codPeriodo 
					AND mD.cod_cuenta = $codCuenta 
        			AND mD.cod_programa = $codPrograma 
        			AND mE.est_modificacion = 'A'";

		$resultM = $this->execute($sqlM);
		$detalleM = $this->getArray($resultM);
				
		foreach ($detalleM as $k => $m) {
			$m['tip_modificacion'] = $this->getTipoModificacion($m['tip_modificacion']);
			array_push($modificaciones, $m);
		}

		// Presupuesto Reservado
		$detalle['reservado']     ['lines']   = $reservado;
		$detalle['reservado']     ['totales']['registros'] = sizeof($reservado);
		$detalle['reservado']     ['totales']['total']     = $this->getTotalDetalle($reservado);
		// Presupuesto Aprobado
		$detalle['aprobado']      ['lines']   = $aprobado;
		$detalle['aprobado']      ['totales']['registros'] = sizeof($aprobado);
		$detalle['aprobado']      ['totales']['total']     = $this->getTotalDetalle($aprobado);
		// Presupuesto Ejecutado
		$detalle['ejecutado']     ['lines']   = $ejecutado;
		$detalle['ejecutado']     ['totales']['registros'] = sizeof($ejecutado);
		$detalle['ejecutado']     ['totales']['total']     = $this->getTotalDetalle($ejecutado);
		// Presupuesto Modificado
		$detalle['modificaciones']['lines']   = $modificaciones;
		$detalle['modificaciones']['totales']['registros'] = sizeof($modificaciones);
		$detalle['modificaciones']['totales']['total']     = $this->getTotalDetalle($modificaciones, true);

		if ($cP != null && $cC != null) {
			return $detalle;
		} else {
			echo json_encode(array('detalle' => $detalle));
		}
		
		
	}

	private function getTipoDocumento($tipo) {
		$name = "";
		switch ($tipo) {
			case 1:
				$name = "Solicitud de Pedido";
				break;
			case 2:
				$name = "Orden de Pago";
				break;
			case 3:
				$name = "Orden Pago Directa";
				break;
			case 4:
				$name = "Transferencia Bancaria";
				break;
			case 5:
				$name = "Documento Egreso";
				break;
		}
		return $name;
	}

	private function getTipoModificacion($tipo) {
		$name = "";
		switch ($tipo) {
			case 1:
				$name = "Modificación Local";
				break;
			case 2:
				$name = "Presupuesto Extraordinario";
				break;
		}
		return $name;
	}

	private function getTotalDetalle($linesDetalle, $isMod = false) {
		
		if ($isMod) {
			
			$total_mon_disminuir = 0;
			$total_mon_aumentar  = 0;
			$total_mon_total     = 0;
			foreach ($linesDetalle as $line) {
				$total_mon_disminuir += $line['mon_disminuir'];
				$total_mon_aumentar  += $line['mon_aumentar'];
				$total_mon_total     += $line['mon_total'];
			}

			$total = array('mon_disminuir' => $total_mon_disminuir, 
						   'mon_aumentar' => $total_mon_aumentar,
						   'mon_total' => $total_mon_total
					 );

		} else {
			$total = 0;
			foreach ($linesDetalle as $line) {
				$total += $line['mon_gasto'];
			}			
		}

		return $total;
	}
}

?>