
require ('BaseController.php');
class PresupuestoController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function getAllCentroCostos() {

        $allCentros = array();

        $sql = "SELECT RTRIM(COD_CENTRO) as COD_CENTRO,  RTRIM(COD_CENTRO_PADRE) as COD_CENTRO_PADRE, 
                       COD_ENCARGADO, COD_PRESUPUESTO, COD_PROGRAMA, RTRIM(DES_CENTRO) as DES_CENTRO 
                FROM RH_CENTROS_COSTO as cc 
                WHERE cc.COD_CENTRO not in ( SELECT ccp.COD_CENTRO_PADRE 
                                             FROM RH_CENTROS_COSTO as ccp 
                                             WHERE ccp.COD_CENTRO_PADRE is not null )";
        $result = $this->execute($sql);

        $centroCostros = $this->getArray($result);

        $initCentro = ['COD_CENTRO' => 0, 'COD_CENTRO_PADRE' => 0, 'COD_ENCARGADO' => 0, 'COD_PRESUPUESTO' => 0, 'COD_PROGRAMA' => 0, 'DES_CENTRO' => 'TODOS'];        
        array_push($allCentros, $initCentro);
        
        foreach ($centroCostros as $cc) {
            array_push($allCentros, $cc);
        }

        echo json_encode(array('list'=>$allCentros, 'first'=>$allCentros[0]));

    }

    public function getYears() {

      $allYears = array();
      $current_year = $this->getCurrentYear();
      $current = ['cod_year'=> $current_year, 'num_year'=> $current_year];

      $minYear = 2004;
      for ($i = $minYear; $i <= $current_year; $i++) {
        $year_data = ['cod_year'=> $i, 'num_year'=>$i];
        array_push($allYears, $year_data);
      }
    
      echo json_encode(array('all'=>$allYears, 'current'=>$current));
    }

    public function getCurrentYear() {
      $sql = "SELECT val_dato 
              FROM SIF_CONFIGURADORES 
              WHERE cod_configurador = 13";

      $result = $this->execute($sql);
      $current_year   = intval($this->getArray($result)[0]['val_dato']);
      return $current_year;
    }

    public function getEncabezado() {
        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $current_year = $this->getCurrentYear();

        $params = $this->getParameters();

        if ($params["year"] > $current_year) {
          $year        = $current_year;
        } else {
          $year        = $params["year"];
        }

        $year          = $this->getCurrentYear();
        $codCentro     = $params["codCentro"];
        $codSubpartida = $params["codSubpartida"];
        $desCuenta     = $params["desCuenta"];

        $filters = "";
        if ($codCentro != "") {
            $filters .= "AND pe.cod_centro like '$codCentro' ";
        }

        if ($codSubpartida != "") {
            $filters .= "AND pe.cod_subpartida like '%' + $codSubpartida + '%' ";
        }

        if ($desCuenta != "") {
            $filters .= "AND pc.DES_CUENTA like '%' + $desCuenta + '%'";
        }   

        $sql = "SELECT pe.ano_presupuesto,  
                       pe.cod_version,  
                       pe.cod_centro,  
                       pe.cod_subpartida,  
                       pe.mon_ordinario,  
                       pe.mon_modificaciones,  
                       pe.mon_presupuesto_modificado,  
                       pe.mon_compromiso_provisional,  
                       pe.mon_compromiso_definitivo,  
                       pe.mon_gasto_real,  
                       pe.mon_disponible,  
                       pc.DES_CUENTA,  
                       pe.COD_META, 
                       cc.DES_CENTRO
                    FROM PRE_EJECUCION_PRESUPUESTO_ENCABE as pe, PRE_CUENTAS as pc, RH_CENTROS_COSTO as cc 
                    WHERE pc.COD_CUENTA = pe.cod_subpartida 
                       AND RTRIM(pe.cod_centro) = RTRIM(cc.COD_CENTRO) 
                       AND pe.ano_presupuesto = $year 
                       $filters";

        $result = $this->execute($sql);
        $encabezado = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $encabezado = $this->toUtf8($encabezado);         
        }

        $totales = array('tot_presupuesto_ordinario'  => 0, 
                         'tot_modificaciones'         => 0, 
                         'tot_total_presupuesto'      => 0, 
                         'tot_compromiso_provisional' => 0, 
                         'tot_compromiso_definitivo'  => 0, 
                         'tot_gasto_real'             => 0, 
                         'tot_disponible'             => 0
                    );

        // First line of detalle
        $firstLine    = 0;      
        $fCodPrograma = 0;
        $fCodCuenta   = 0;
        if(sizeof($encabezado) > 0) {
          foreach ($encabezado as $k => $line) {
              if ($firstLine == 0) {
                  $nYear          = $line['ano_presupuesto']; 
                  $nCodCentro     = $line['cod_centro'];
                  $nCodSubpartida = $line['cod_subpartida'];
                  $nCodMeta       = $line['COD_META'];
              }
              $totales['tot_presupuesto_ordinario']  += $line['mon_ordinario'];
              $totales['tot_modificaciones']         += $line['mon_modificaciones'];

              $nTotalPresupuesto = $line['mon_ordinario'] - $line['mon_modificaciones'];
              $totales['tot_total_presupuesto']      += $nTotalPresupuesto;

              $totales['tot_compromiso_provisional'] += $line['mon_compromiso_provisional'];
              $totales['tot_compromiso_definitivo']  += $line['mon_compromiso_definitivo'];
              $totales['tot_gasto_real']             += $line['mon_gasto_real'];
              $totales['tot_disponible']             += $line['mon_disponible'];

              $firstLine++;
          }

          $initDetalle = $this->getDetalle($nYear, $nCodCentro, $nCodSubpartida, $nCodMeta);

          if ($connectionType == "odbc_mssql") {              
              echo json_encode(array('encabezado' => $encabezado, 'totales' => $totales, 'registros' => sizeof($encabezado), 'initDetalle' => $initDetalle), JSON_UNESCAPED_UNICODE);
          } else {
              echo json_encode(array('encabezado' => $encabezado, 'totales' => $totales, 'registros' => sizeof($encabezado), 'initDetalle' => $initDetalle));
          }          

        } else {

          echo json_encode(array('encabezado' => array(), 'totales' => array(), 'registros' => sizeof($encabezado), 'initDetalle' => array()));          

        }

        /*
        $conditions = "AND cod_programa = $codPrograma 
            AND cod_cuenta = $codCuenta;"
        */

        /*
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
        */
    }

    public function getDetalle($nYear = null, $nCodCentro = null, 
                               $nCodSubpartida = null, $nCodMeta = null) {
        
        if ($nYear != null && $nCodCentro != null && $nCodSubpartida != null && $nCodMeta != null) {
            $year          = $nYear;
            $codCentro     = $nCodCentro;
            $codSubpartida = $nCodSubpartida;
            $codMeta       = $nCodMeta;

            $connectionType = $_SESSION["CONNECTION_TYPE"];

        } else {
            $params = $this->getParameters();
            $year          = $params["year"];
            $codCentro     = $params["codCentro"];
            $codSubpartida = $params["codSubpartida"];
            $codMeta       = $params["codMeta"];

            session_start();
            $connectionType = $_SESSION["CONNECTION_TYPE"];            
        }

        // Tabs Provisional, Definitivo, Real
        $provisional = array ();
        $definitivo  = array ();
        $real        = array ();

        // Compromiso Provisional
        $sql = "SELECT ano_presupuesto, cod_version, cod_centro, cod_detalle, cod_subpartida, 
                       num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, 
                       des_observacion, COD_META 
                FROM PRE_EJECUCION_PRESUPUESTO_DETALL 
                WHERE ano_presupuesto = '$year' 
                   AND cod_centro like '$codCentro' + '%' 
                   AND cod_subpartida = '$codSubpartida' 
                   AND tip_detalle = 'CP' 
                   AND COD_META = '$codMeta'";

        $result      = $this->execute($sql);
        $provisional = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $provisional = $this->toUtf8($provisional);         
        }        
        
        if (sizeof($provisional) > 0) {
            $nProvisional = array();    
            foreach ($provisional as $k => $line) {

                //$line['tip_doc'] = intval($line['tip_documento']);
                //$line['tip_documento'] = $this->getTipoDocumento($line['tip_documento']);           
                $line['desTipDocumento'] = $this->getTipoDocumento($line['tip_documento']);
                $line['fechaDetalle'] = $this->getFechaDetalle($line['tip_documento'], $line['num_documento'], $line['mon_gasto']);
                array_push($nProvisional, $line);

            }
            $provisional = $nProvisional;
        }
        
        // Compromiso Definitivo
        $sql = "SELECT ano_presupuesto, cod_version, cod_centro, cod_detalle, cod_subpartida, 
                       num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, 
                       des_observacion, COD_META 
                FROM PRE_EJECUCION_PRESUPUESTO_DETALL 
                WHERE ano_presupuesto = '$year' 
                   AND cod_centro like '$codCentro' + '%' 
                   AND cod_subpartida = '$codSubpartida' 
                   AND tip_detalle = 'CD' 
                   AND COD_META = '$codMeta'";

        $result      = $this->execute($sql);
        $definitivo  = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $definitivo = $this->toUtf8($definitivo);         
        }           

        if (sizeof($definitivo) > 0) {
            $nDefinitivo = array();    
            foreach ($definitivo as $k => $line) {

                //$line['tip_doc'] = intval($line['tip_documento']);
                //$line['tip_documento'] = $this->getTipoDocumento($line['tip_documento']);           
                $line['desTipDocumento'] = $this->getTipoDocumento($line['tip_documento']);
                $line['fechaDetalle'] = $this->getFechaDetalle($line['tip_documento'], $line['num_documento'], $line['mon_gasto']);
                array_push($nDefinitivo, $line);

            }
            $definitivo = $nDefinitivo;
        }        

        // Gasto Real
        $sql = "SELECT ano_presupuesto, cod_version, cod_centro, cod_detalle, cod_subpartida, 
                       num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, 
                       des_observacion, COD_META 
                FROM PRE_EJECUCION_PRESUPUESTO_DETALL 
                WHERE ano_presupuesto = '$year' 
                   AND cod_centro like '$codCentro' + '%' 
                   AND cod_subpartida = '$codSubpartida' 
                   AND tip_detalle = 'GR' 
                   AND COD_META = '$codMeta'";

        $result = $this->execute($sql);
        $real   = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $real = $this->toUtf8($real);         
        }          

        if (sizeof($real) > 0) {
            $nReal = array();    
            foreach ($real as $k => $line) {

                //$line['tip_doc'] = intval($line['tip_documento']);
                $line['desTipDocumento'] = $this->getTipoDocumento($line['tip_documento']);
                $line['fechaDetalle'] = $this->getFechaDetalle($line['tip_documento'], $line['num_documento'], $line['mon_gasto']);
                array_push($nReal, $line);

            }
            $real = $nReal;
        }

        //Tab Modificaciones
        $modificaciones = array ();

        $sqlM = "SELECT me.num_modificacion,  
                      me.fec_modificacion,  
                      me.tip_modificacion,   
                      me.ano_presupuesto,  
                      md.mon_total,  
                      md.cod_detalle,  
                      md.mon_aumentar,  
                      md.mon_disminuir 
                 FROM PRE_MODIFICACIONES_ENCABEZADO as me,  
                      PRE_MODIFICACIONES_DETALLE as md 
                 WHERE me.num_modificacion = md.num_modificacion 
                    AND me.tip_modificacion = md.tip_modificacion 
                    AND me.ano_presupuesto = md.ano_presupuesto 
                    AND md.ano_presupuesto = '$year' 
                    AND md.cod_centro like '$codCentro' + '%' 
                    AND md.cod_subpartida = '$codSubpartida' 
                    AND me.est_modificacion = 'A' 
                    AND md.cod_meta = '$codMeta' 
                 ORDER BY me.fec_modificacion ASC";

        $resultM = $this->execute($sqlM);
        $detalleM = $this->getArray($resultM);

        if ($connectionType == "odbc_mssql") {
            $detalleM = $this->toUtf8($detalleM);         
        }           
                  
        foreach ($detalleM as $k => $m) {
            $m['tip_modificacion'] = $this->getTipoModificacion($m['tip_modificacion']);
            array_push($modificaciones, $m);
        }
        
        // Presupuesto Provisional
        $detalle['provisional'] ['lines']   = $provisional;
        $detalle['provisional'] ['totales']['registros'] = sizeof($provisional);
        $detalle['provisional'] ['totales']['total']     = $this->getTotalDetalle($provisional);
        // Presupuesto Definitivo
        $detalle['definitivo']  ['lines']   = $definitivo;
        $detalle['definitivo']  ['totales']['registros'] = sizeof($definitivo);
        $detalle['definitivo']  ['totales']['total']     = $this->getTotalDetalle($definitivo);
        // Presupuesto Real
        $detalle['real']        ['lines']   = $real;
        $detalle['real']        ['totales']['registros'] = sizeof($real);
        $detalle['real']        ['totales']['total']     = $this->getTotalDetalle($real);

        // Presupuesto Modificado
        $detalle['modificaciones']['lines']   = $modificaciones;
        $detalle['modificaciones']['totales']['registros'] = sizeof($modificaciones);
        $detalle['modificaciones']['totales']['total']     = $this->getTotalDetalle($modificaciones, true);

        if ($nYear != null && $nCodCentro != null && $nCodSubpartida != null && $nCodMeta != null) {
          return $detalle;
        } else {
           
            if ($connectionType == "odbc_mssql") {              
              echo json_encode(array('detalle' => $detalle), JSON_UNESCAPED_UNICODE);
            }else {
              echo json_encode(array('detalle' => $detalle));
            }
                
        }
        
    }

    private function getTipoDocumento($tipo) {

        $name = "";
        $sql = "SELECT cod_tipo, des_tipo 
                FROM PRE_TIPOS_DOCUMENTOS_AFECTACION
                WHERE cod_tipo = '$tipo'";

        $result = $this->execute($sql);
        $data   = $this->getArray($result);

        $name = $data[0]['des_tipo'];

        return $name;
    }

    private function getFechaDetalle($tipDocumento, $numDocumento, $monGasto) {

        $fecha = "";

        switch ($tipDocumento) {

            // Otros Documentos
            case 2: 
                    $sql = "SELECT fec_documento, fec_registro 
                            FROM PRE_OTROS_DOCUMENTOS_ENCABEZADO 
                            WHERE num_documento = '$numDocumento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    $fecha = $data[0]['fec_documento'];
                break;

            // Ordenes de Compra
            case 3: 
                    $sql = "SELECT fec_orden 
                            FROM PRO_ORDENES_COMPRA  
                            WHERE cod_orden = '$numDocumento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    $fecha = $data[0]['fec_orden'];
                break;

            // Ordenes de Pago
            case 8: 
                    $sql = "SELECT fec_orden 
                            FROM TES_ORDEN_PAGO_ENCABEZADO 
                            WHERE cod_orden_pago = '$numDocumento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    $fecha = $data[0]['fec_orden'];
                break;

            // Cheques
            case 9:
                    $sql = "SELECT fec_cheque, fec_anulacion, cod_estado
                            FROM TES_CHEQUES_ENCABEZADO 
                            WHERE num_cheque = '$numDocumento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    if ($data[0]['cod_estado'] == 3 && $monGasto < 0) {
                         $fecha = $data[0]['fec_anulacion'];
                    } else {
                        $fecha = $data[0]['fec_cheque'];
                    }
                break;

            // Liquidación de Viáticos
            case 11:
                    $sql = "SELECT fec_comprobante 
                            FROM TES_CCHV_COMPROBANTE_ENCABEZADO 
                            WHERE num_comprobante = '$num_documento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    $fecha = $data[0]['fec_comprobante'];
                break;

            //Transferencia Bancaria
            case 18:
                    $sql = "SELECT fec_aplicacion, ind_estado
                            FROM TES_PLANILLA_TRANSFERENCIAS   
                            WHERE num_documento = '$num_documento'";

                    $result = $this->execute($sql);
                    $data   = $this->getArray($result);

                    if ($data[0]['ind_estado'] == 3 && $monGasto < 0) {

                        $sql = "SELECT fec_anulacion 
                                FROM TES_PLANILLA_TRANS_ANULACION   
                                WHERE num_documento = '$num_documento'";
                        $result = $this->execute($sql);
                        $data   = $this->getArray($result);

                        $fecha = $data[0]['fec_anulacion'];

                    } else {
                        $fecha = $data[0]['fec_aplicacion'];
                    }
                break;
        }

        return $fecha;

    }

    private function getTipoModificacion($tipo) {
        $name = "";

        $sql = "SELECT tm.cod_tipo,  
                       tm.des_tipo,  
                       tm.ind_activo 
                FROM PRE_TIPOS_MODIFICION as tm  
                WHERE tm.ind_activo = 1 
                AND tm.cod_tipo = '$tipo'";

        $result = $this->execute($sql);
        $data   = $this->getArray($result);

        $name = $data[0]['des_tipo'];        

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