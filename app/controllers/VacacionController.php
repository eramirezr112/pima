<?php 

require ('BaseController.php');
class VacacionController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function all() {

        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['RH_SOLICITUD_VACACIONES', 'alias'=>'v'];

        $columns = [
            'num_solicitud' =>'solicitud',            
            "$nameSolicitante" => 'solicitante',
            'fec_confeccion' =>'fecha',
            'dias_solicitados' =>'dias solicitados',
            'fec_inicio' =>'desde',
            'fec_final' =>'hasta',
            'cod_estado' =>'estado'
        ];

        $conditions = [
            //'limit' => 10,
            'where' => [
                ['cod_estado' => 'C'],
                ['in' => 
                    ['cod_funcionario' => $_SESSION['FUNCIONARIOS_A_CARGO']]
                ]               
            ]
        ];

        $relations = [
            'join' => [
                array ('RH_FUNCIONARIOS', //table
                       'cod_funcionario', //columns
                       'ssf' //alias
                )/*,
                array ('RH_CENTROS_COSTO', //table
                       'cod_centro', //column
                       'rcc' //alias
                ),
                array ('PRE_PROGRAMAS', //table
                       'cod_programa', //column
                       'p' //alias
                ),*/
            ]
        ];

        //$fields = $this->prepareFields($columns);

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $solicitudes = $this->getArray($result);

        echo json_encode(array('columns'=>$columns,'vacaciones'=>$solicitudes));
    }

    public function getGuardadas() {

        session_start();
        //$isAdmin     = $_SESSION['is_admin'];
        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $codPeriodo  = $_SESSION['cod_periodo'];
        $codPrograma = $_SESSION['cod_programa'];
        $isJefe      = $_SESSION['rol_web'];
        
        if ($isJefe == 1 || $isJefe == 2) {
            $statusFilter = "AND e.cod_usuario = $codUsuario AND e.ind_estado in (1)";      
            $condition = "WHERE e.cod_periodo = $codPeriodo $statusFilter";

            $sql = "SELECT e.*, p.des_programa 
                    FROM frm_solic_pedido_enca as e, frm_programas as p $condition 
                    AND e.cod_programa = p.cod_programa 
                    AND e.cod_periodo = p.cod_periodo 
                    ORDER BY e.COD_SOLICITUD DESC";
            $result = $this->execute($sql);
            $solicitudes = $this->getArray($result);

            echo json_encode(array('solicitudesG'=>$solicitudes));
        } else {
            echo json_encode(array('solicitudesG'=>array()));
        }
    }

    public function get() {
        $params = $this->getParameters();
        $id = $params["idSolicitud"];

        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }        

        $sql = "SELECT v.num_solicitud, 
                       v.cod_funcionario, 
                       (select $nameFuncionario from rh_funcionarios as ssf where ssf.cod_funcionario = v.cod_funcionario) as funcionario,
                       v.cod_centro, 
                       v.fec_confeccion, 
                       v.dias_solicitados, 
                       v.fec_inicio, 
                       v.fec_final, 
                       v.cod_estado, 
                       (select f.num_expediente from rh_funcionarios as f where f.cod_funcionario = v.cod_funcionario) as num_expediente, 
                       (select f.fec_acreditacion from rh_funcionarios as f where f.cod_funcionario = v.cod_funcionario) as fec_acreditacion, 
                       v.des_observaciones, 
                       v.tip_boleta, 
                       v.cod_jefatura, 
                       v.cod_autoriza, 
                       v.fec_jefatura, 
                       v.fec_autoriza 
                FROM RH_SOLICITUD_VACACIONES as v 
                WHERE num_solicitud = $id";        
        $result = $this->execute($sql);
        $solicitud = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
          $solicitud = $this->toUtf8($solicitud);          
        }  

        $codFuncionario = $solicitud[0]["cod_funcionario"];

        $sql = "SELECT rva.NUM_PERIODO, 
                       rva.NUM_SALDO_PERIODO, 
                       rva.NUM_VAC_GASTADAS, 
                       rva.COD_FUNCIONARIO, 
                       rva.NUM_VAC_COMPENSADAS 
                FROM RH_VACACIONES_ACREDITACION as rva
                WHERE rva.COD_FUNCIONARIO = $codFuncionario 
                    AND rva.NUM_SALDO_PERIODO > 0 
                    ORDER BY rva.NUM_PERIODO ASC";
        $result = $this->execute($sql);
        $saldoActual = $this->getArray($result);

        $sql = "SELECT NUM_SOLICITUD, NUM_PERIODO, NUM_DIAS 
                FROM RH_SOLICITUD_VACACIONES_DETALLE 
                WHERE NUM_SOLICITUD = $id 
                ORDER BY NUM_PERIODO ASC";
        $result = $this->execute($sql);
        $diasGastados = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            echo json_encode(array('solicitud'=>$solicitud, 'saldoActual'=>$saldoActual, 'diasGastados'=>$diasGastados), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('solicitud'=>$solicitud, 'saldoActual'=>$saldoActual, 'diasGastados'=>$diasGastados));
        }
    }

    public function getMaxNum() {
        $sql = "SELECT MAX(cod_solicitud) as cod_solicitud FROM frm_solic_pedido_enca";
        $result = $this->execute($sql);
        $maxNum = $this->getArray($result);

        echo json_encode(array('maxNum'=>$maxNum));
    }

    public function add() {
        $params = $this->getParameters();

        // Se obtiene de nuevo el maximo registro
        //========================================
        $sql = "SELECT MAX(cod_solicitud) as cod_solicitud FROM frm_solic_pedido_enca";
        $result = $this->execute($sql);
        $maxNum = $this->getArray($result);
        $newCodSolicitud = $maxNum[0]["cod_solicitud"] + 1;

        // Save Encabezado
        //========================================      
        $encabezado = json_decode($params['encabezado']);
        $codSolicitud   = $newCodSolicitud;
        $codPeriodo     = $encabezado->cod_periodo;
        $codPrograma    = $encabezado->cod_programa;
        $fecRegistro    = $encabezado->fec_registro;
        $codUsuario     = $encabezado->cod_usuario;
        $desObservacion = $encabezado->des_observacion;
        $indEstado      = $encabezado->ind_estado;
        $monTotal       = $encabezado->mon_total;
        $monSaldo       = $encabezado->mon_total;
        $indMoneda      = $encabezado->ind_moneda;      
        $codProveedor   = $encabezado->cod_proveedor;
        $tipoCambio     = $encabezado->tipo_cambio;

        $monTotalMonex = 'NULL';
        $monSaldoMonex = 'NULL';
        $columnMonSaldoMonex = "";
        $columnTipoCambio = "";
        $valueTipoCambio = "";
        if($indMoneda == 2) {
            $monTotalMonex = $monTotal * $tipoCambio;
            $monSaldoMonex = $monTotalMonex;
            $columnMonSaldoMonex = ", mon_saldo_monex";
            $columnTipoCambio = ", mon_tipo_cambio";
            $valueTipoCambio = ", $tipoCambio"; 
        }

        $withColumn = "";
        $withValue = "";
        if ($codProveedor != 0) {
            $withColumn = "cod_proveedor, ";
            $withValue = "$codProveedor, ";
        }
        $columnList = "cod_solicitud, cod_periodo, cod_programa, $withColumn 
                       fec_registro, cod_usuario, des_observacion, ind_estado, mon_total, 
                       ind_moneda, mon_saldo, mon_total_monex, mon_saldo_monex $columnTipoCambio";
        $valuesList = "$codSolicitud, $codPeriodo, $codPrograma, $withValue 
                       GETDATE(), $codUsuario, '$desObservacion', $indEstado, $monTotal, 
                       $indMoneda, $monSaldo, $monTotalMonex, $monSaldoMonex $valueTipoCambio";     

        $qEncabezado = "INSERT INTO frm_solic_pedido_enca ($columnList) VALUES ($valuesList)";
        $this->execute($qEncabezado);

        // Save Detalle
        //========================================
        $detalle = json_decode($params['detalle']);
        $dataDetalle = $detalle->data;
        $qDetalle = "INSERT INTO frm_solic_pedido_deta (cod_solicitud, cod_detalle, cod_cuenta, des_detalle, can_producto, mon_unidad, mon_detalle, mon_detalle_monex, mon_saldo $columnMonSaldoMonex) VALUES ";
        $listValues = "";
        $nLine = 1; 
        foreach ($dataDetalle as $key => $line) {

            $cantidad = $line->cantidad;    
            $codCuenta = $line->cod_cuenta; 
            $desCuenta = $line->des_cuenta; 
            $descripcion = $line->descripcion;  
            $monDisponible = $line->mon_disponible; 
            $numCuenta = $line->num_cuenta; 
            $preUnit = $line->preUnit;  
            $totLine = $line->totLineh;
            $monDetalleMonex = 'NULL';
            $monSaldoMonex = "";
            if($indMoneda == 2) {
                $monDetalleMonex = $totLine * $tipoCambio;
                $monSaldoMonex = ", $monDetalleMonex";
            }
            $listValues .= "($newCodSolicitud, $nLine, $codCuenta, '$descripcion', $cantidad, $preUnit, $totLine, $monDetalleMonex, $totLine $monSaldoMonex), "; 

            $nLine++;
        }

        $listValues = substr_replace($listValues, ";", -2);
        $qDetalle .= $listValues;

        $this->execute($qDetalle);

        echo json_encode(array('query'=>$qDetalle));
    }

    public function approve() {
        session_start();
        $codAprobador = $_SESSION['COD_FUNCIONARIO'];

        $params = $this->getParameters();
        
        $codFuncionario = $params['codFuncionario'];
        $id = $params['numSolicitud'];

        $newSaldoPeriodo = json_decode($params['newSaldoPeriodo']);

        $listQuerys = array();
        
        foreach ($newSaldoPeriodo as $sPeriodo) {

            $strUpdate = "UPDATE RH_VACACIONES_ACREDITACION ";

            $numSaldoPeriodo = number_format(floatval($sPeriodo->NUM_SALDO_PERIODO),2);
            $numVacGastadas  = number_format(floatval($sPeriodo->NUM_VAC_GASTADAS),2);

            $nNumSaldoPeriodo = $numSaldoPeriodo - $sPeriodo->DAYS_REQUEST;
            $nNumVacGastadas  = $numVacGastadas + $sPeriodo->DAYS_REQUEST;

            $strUpdate .= "SET NUM_SALDO_PERIODO = ".number_format($nNumSaldoPeriodo,2).", NUM_VAC_GASTADAS = ".number_format($nNumVacGastadas,2)." ";

            $strUpdate .= "WHERE NUM_PERIODO = ".$sPeriodo->NUM_PERIODO." AND COD_FUNCIONARIO = $codFuncionario;";

            array_push($listQuerys, $strUpdate);
        
        }

        $status = true;

        foreach ($listQuerys as $query) {
            if(!$this->execute($query)) {
                $status = false;
            }
        }

        if ($status) {

            $sql = "UPDATE RH_SOLICITUD_VACACIONES SET cod_estado = 'A', cod_jefatura = $codAprobador, fec_jefatura = GETDATE() WHERE num_solicitud = $id";
            if(!$this->execute($sql)) {
                $status = false;
            }

            array_push($listQuerys, $sql);

        }

        echo json_encode(array('status'=>$status, 'query' => $listQuerys));

    }

    public function changeStatus() {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];
        $status = $params['nStatus'];

        $set = "";
        if($status == 2){
            session_start();            
            $codUsuario = $_SESSION['cod_usuario'];         
            $set = ", cod_usuario_en = $codUsuario, fec_envia = GETDATE()";
        }

        if($status == 3){
            session_start();            
            $codUsuario = $_SESSION['cod_usuario'];         
            $set = ", cod_usuario_aut = $codUsuario, fec_autoriza = GETDATE()";
        }

        if($status == 4){
            session_start();            
            $codUsuario = $_SESSION['cod_usuario'];         
            $set = ", cod_usuario_re = $codUsuario, fec_rechaza = GETDATE()";
        }

        if($status == 5){
            session_start();            
            $codUsuario = $_SESSION['cod_usuario'];         
            $set = ", cod_usuario_daf = $codUsuario, fec_aprueba = GETDATE()";
        }

        if($status == 6){
            session_start();            
            $codUsuario = $_SESSION['cod_usuario'];         
            $set = ", cod_usuario_vb = $codUsuario, fec_vistob = GETDATE()";
        }

        $update = "UPDATE frm_solic_pedido_enca SET ind_estado = $status $set WHERE cod_solicitud = $codSolicitud";
        $this->execute($update);

        echo json_encode(array('result'=>$update));
    }

    public function verifySolicitud()
    {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];

        // Encabezado de la solicitud
        $sql = "SELECT cod_solicitud, cod_periodo, cod_programa, ind_moneda 
                FROM frm_solic_pedido_enca   
                WHERE cod_solicitud = $codSolicitud";       
        $result = $this->execute($sql);
        $solicitud = $this->getArray($result);
        
        $codPeriodo  = $solicitud[0]["cod_periodo"];
        $codPrograma = $solicitud[0]["cod_programa"];
        $indMoneda   = $solicitud[0]["ind_moneda"];


        // Detalle de la solicitud
        $columns = array(
            "CAST(d.CAN_PRODUCTO AS INT) as cantidad, ",
            "d.COD_CUENTA as cod_cuenta, ",
            "d.DES_DETALLE as descripcion, ",
            "d.MON_DETALLE as totLine, ",
            "d.MON_DETALLE_MONEX as monDetalleMonex, ",
            "d.MON_UNIDAD as preUnit "
        );
        $listColumns = "";
        foreach ($columns as $column) {
            $listColumns .= $column;
        }

        $sqlDetalle = "SELECT $listColumns, ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
                       FROM frm_solic_pedido_deta as d, frm_solic_pedido_enca as se, 
                            frm_ejecucion_presupuesto_encabe as ep,
                            frm_cuentas as c
                       WHERE d.cod_solicitud = $codSolicitud 
                           AND se.cod_solicitud = d.cod_solicitud                          
                           AND ep.cod_periodo = se.cod_periodo 
                           AND ep.cod_programa = se.cod_programa 
                           AND ep.cod_cuenta = c.cod_cuenta 
                           AND ep.cod_cuenta = d.cod_cuenta                            
                       order by d.COD_DETALLE ASC";     
        $resultDetalle = $this->execute($sqlDetalle);
        $allLines = $this->getArray($resultDetalle);

        $lineErrors = array();
        $isSuperior = false;
        foreach ($allLines as $key => $line) {

            $monCurrent = $line["totLine"];
            if ($indMoneda == 2) {
                $monCurrent = $line["monDetalleMonex"];
            }

            if ($monCurrent > $line["mon_disponible"]){
                $isSuperior = true;
                $nLinea = $key + 1;

                array_push($lineErrors, $nLinea);
            }       
        }

        if (!$isSuperior) {
            $result = array('status' => 'OK', 'codPeriodo' => $codPeriodo, 'codPrograma' => $codPrograma, 'moneda' => $indMoneda);
        } else {
            $result = array('status' => 'ERROR', 'lineErrors'=>$lineErrors);
        }
        
        echo json_encode($result);  

    }

    public function startAfectacionPresupuestaria()
    {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];
        $codPeriodo   = $params['codPeriodo'];
        $codPrograma  = $params['codPrograma'];
        $moneda       = $params['moneda'];

        // Detalle de la solicitud
        $columns = array(
            "CAST(d.CAN_PRODUCTO AS INT) as cantidad, ",
            "d.COD_CUENTA as cod_cuenta, ",
            "d.DES_DETALLE as descripcion, ",
            "d.MON_DETALLE as totLine, ",
            "d.MON_DETALLE_MONEX as monDetalleMonex, ",
            "d.MON_UNIDAD as preUnit "
        );
        $listColumns = "";
        foreach ($columns as $column) {
            $listColumns .= $column;
        }

        $sqlDetalle = "SELECT $listColumns, ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
                       FROM frm_solic_pedido_deta as d, frm_solic_pedido_enca as se, 
                            frm_ejecucion_presupuesto_encabe as ep,
                            frm_cuentas as c
                       WHERE d.cod_solicitud = $codSolicitud 
                           AND se.cod_solicitud = d.cod_solicitud                          
                           AND ep.cod_periodo = se.cod_periodo 
                           AND ep.cod_programa = se.cod_programa 
                           AND ep.cod_cuenta = c.cod_cuenta 
                           AND ep.cod_cuenta = d.cod_cuenta                            
                       order by d.COD_DETALLE ASC";     
        //echo $sqlDetalle;
        $resultDetalle = $this->execute($sqlDetalle);
        $allLines = $this->getArray($resultDetalle);

        foreach ($allLines as $key => $line) {

            $monto = $line["totLine"];
            if ($moneda == 2) {
                $monto = $line["monDetalleMonex"];
            }
            
            $codCuenta = $line["cod_cuenta"];

            $sql = "SELECT mon_compromiso_provisional, mon_disponible  
                    FROM FRM_EJECUCION_PRESUPUESTO_ENCABE 
                    WHERE cod_periodo = $codPeriodo  
                        AND cod_programa = $codPrograma 
                        AND cod_cuenta = $codCuenta;";      
            $result = $this->execute($sql);
            $data = $this->getArray($result);

            $newCompromisoProvicional = $data[0]['mon_compromiso_provisional'] + $monto;
            $newMontoDisponible = $data[0]['mon_disponible'] - $monto;

            $update = "UPDATE FRM_EJECUCION_PRESUPUESTO_ENCABE 
                            SET mon_compromiso_provisional = $newCompromisoProvicional, 
                                mon_disponible = $newMontoDisponible   
                        WHERE cod_periodo = $codPeriodo  
                            AND cod_programa = $codPrograma  
                            AND cod_cuenta = $codCuenta;";

            //echo "LINEA: ". ($key+1) ."<br />";       
            //echo "-------------------------<br />";   
            //echo "UPDATE: ( ".$update ." ) <br /><br />"; 
            //echo "================================="; 
            $this->execute($update);

            //$n = $key + 1;
            $insert = "INSERT INTO FRM_EJECUCION_PRESUPUESTO_DETALL (COD_PERIODO, 
                        COD_PROGRAMA, COD_CUENTA, num_documento, tip_documento, tip_detalle, mon_gasto, 
                        fec_documento)  
                       VALUES ($codPeriodo, $codPrograma, $codCuenta, $codSolicitud, 1, 'CP', $monto, GETDATE());";
            $this->execute($insert);
            //echo "LINEA: ". $n ."<br />";     
            //echo "-------------------------<br />";   
            //echo "INSERT DETALLE: ( ".$insert ." ) <br /><br />"; 
            //echo "=================================";                    

        }


    }

    public function rejectAfectacionPresupuestaria()
    {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];

        // Encabezado de la solicitud
        $sql = "SELECT cod_solicitud, cod_periodo, cod_programa, ind_moneda 
                FROM frm_solic_pedido_enca   
                WHERE cod_solicitud = $codSolicitud";       
        $result = $this->execute($sql);
        $solicitud = $this->getArray($result);
        
        $codPeriodo  = $solicitud[0]["cod_periodo"];
        $codPrograma = $solicitud[0]["cod_programa"];
        $indMoneda   = $solicitud[0]["ind_moneda"];

        // Detalle de la solicitud
        $columns = array(
            "CAST(d.CAN_PRODUCTO AS INT) as cantidad, ",
            "d.COD_CUENTA as cod_cuenta, ",
            "d.DES_DETALLE as descripcion, ",
            "d.MON_DETALLE as totLine, ",
            "d.MON_DETALLE_MONEX as monDetalleMonex, ",
            "d.MON_UNIDAD as preUnit "
        );
        $listColumns = "";
        foreach ($columns as $column) {
            $listColumns .= $column;
        }

        $sqlDetalle = "SELECT $listColumns, ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
                       FROM frm_solic_pedido_deta as d, frm_solic_pedido_enca as se, 
                            frm_ejecucion_presupuesto_encabe as ep,
                            frm_cuentas as c
                       WHERE d.cod_solicitud = $codSolicitud 
                           AND se.cod_solicitud = d.cod_solicitud                          
                           AND ep.cod_periodo = se.cod_periodo 
                           AND ep.cod_programa = se.cod_programa 
                           AND ep.cod_cuenta = c.cod_cuenta 
                           AND ep.cod_cuenta = d.cod_cuenta                            
                       order by d.COD_DETALLE ASC";     
        $resultDetalle = $this->execute($sqlDetalle);
        $allLines = $this->getArray($resultDetalle);


        foreach ($allLines as $key => $line) {

            $monto = $line["totLine"];
            if ($indMoneda == 2) {
                $monto = $line["monDetalleMonex"];
            }

            $codCuenta = $line["cod_cuenta"];

            $sql = "SELECT mon_compromiso_provisional, mon_disponible  
                    FROM FRM_EJECUCION_PRESUPUESTO_ENCABE 
                    WHERE cod_periodo = $codPeriodo  
                        AND cod_programa = $codPrograma 
                        AND cod_cuenta = $codCuenta;";      
            $result = $this->execute($sql);
            $data = $this->getArray($result);

            $newCompromisoProvicional = $data[0]['mon_compromiso_provisional'] - $monto;
            $newMontoDisponible = $data[0]['mon_disponible'] + $monto;

            $update = "UPDATE FRM_EJECUCION_PRESUPUESTO_ENCABE 
                            SET mon_compromiso_provisional = $newCompromisoProvicional, 
                                mon_disponible = $newMontoDisponible   
                        WHERE cod_periodo = $codPeriodo  
                            AND cod_programa = $codPrograma  
                            AND cod_cuenta = $codCuenta;";
            $this->execute($update);

            //$n = $key + 1;
            $insert = "INSERT INTO FRM_EJECUCION_PRESUPUESTO_DETALL (COD_PERIODO, 
                        COD_PROGRAMA, COD_CUENTA, num_documento, tip_documento, tip_detalle, mon_gasto, 
                        fec_documento)  
                       VALUES ($codPeriodo, $codPrograma, $codCuenta, $codSolicitud, 1, 'CP', -$monto, GETDATE());";
            $this->execute($insert);

        }


    }

    public function setCompromisoAprobado()
    {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];

        // Encabezado de la solicitud
        $sql = "SELECT cod_solicitud, cod_periodo, cod_programa, ind_moneda 
                FROM frm_solic_pedido_enca   
                WHERE cod_solicitud = $codSolicitud";       
        $result = $this->execute($sql);
        $solicitud = $this->getArray($result);
        
        $codPeriodo  = $solicitud[0]["cod_periodo"];
        $codPrograma = $solicitud[0]["cod_programa"];
        $indMoneda   = $solicitud[0]["ind_moneda"];

        // Detalle de la solicitud
        $columns = array(
            "CAST(d.CAN_PRODUCTO AS INT) as cantidad, ",
            "d.COD_CUENTA as cod_cuenta, ",
            "d.DES_DETALLE as descripcion, ",
            "d.MON_DETALLE as totLine, ",
            "d.MON_DETALLE_MONEX as monDetalleMonex, ",
            "d.MON_UNIDAD as preUnit "
        );
        $listColumns = "";
        foreach ($columns as $column) {
            $listColumns .= $column;
        }

        $sqlDetalle = "SELECT $listColumns, ep.mon_disponible, c.cod_cuenta, c.num_cuenta, c.des_cuenta 
                       FROM frm_solic_pedido_deta as d, frm_solic_pedido_enca as se, 
                            frm_ejecucion_presupuesto_encabe as ep,
                            frm_cuentas as c
                       WHERE d.cod_solicitud = $codSolicitud 
                           AND se.cod_solicitud = d.cod_solicitud                          
                           AND ep.cod_periodo = se.cod_periodo 
                           AND ep.cod_programa = se.cod_programa 
                           AND ep.cod_cuenta = c.cod_cuenta 
                           AND ep.cod_cuenta = d.cod_cuenta                            
                       order by d.COD_DETALLE ASC";     
        $resultDetalle = $this->execute($sqlDetalle);
        $allLines = $this->getArray($resultDetalle);

        foreach ($allLines as $key => $line) {

            $monto = $line["totLine"];
            if ($indMoneda == 2) {
                $monto = $line["monDetalleMonex"];
            }

            $codCuenta = $line["cod_cuenta"];

            $sql = "SELECT mon_compromiso_provisional, mon_compromiso_definitivo  
                    FROM FRM_EJECUCION_PRESUPUESTO_ENCABE 
                    WHERE cod_periodo = $codPeriodo  
                        AND cod_programa = $codPrograma 
                        AND cod_cuenta = $codCuenta;";      
            $result = $this->execute($sql);
            $data = $this->getArray($result);

            $newCompromisoProvicional = $data[0]['mon_compromiso_provisional'] - $monto;
            $newCompromisoDefinitivo = $data[0]['mon_compromiso_definitivo'] + $monto;

            $update = "UPDATE FRM_EJECUCION_PRESUPUESTO_ENCABE 
                            SET mon_compromiso_provisional = $newCompromisoProvicional, 
                                mon_compromiso_definitivo = $newCompromisoDefinitivo   
                        WHERE cod_periodo = $codPeriodo  
                            AND cod_programa = $codPrograma  
                            AND cod_cuenta = $codCuenta;";
            $this->execute($update);

            $n = $key + 1;
            $update2 = "UPDATE FRM_EJECUCION_PRESUPUESTO_DETALL 
                            SET tip_detalle = 'CD',
                                fec_documento = GETDATE()
                        WHERE cod_detalle = $n
                            AND cod_periodo = $codPeriodo  
                            AND cod_programa = $codPrograma  
                            AND cod_cuenta = $codCuenta;";
            $this->execute($update2);

        }


    }   

    public function getTipoCambio()
    {
        $sql = "SELECT mon_tipo FROM frm_tipo_cambio WHERE fec_tipo = CONVERT(date, GETDATE(), 112)";
        $result = $this->execute($sql);
        $tipoCambio = $this->getArray($result);

        $result = 0;        
        if (sizeof($tipoCambio) > 0){
            $result = $tipoCambio[0]['mon_tipo'];
        }

        echo json_encode(array('tipoCambio'=>floatval($result)));
    }

    public function validateFactura() {
        $params = $this->getParameters();
        $codSolicitud = $params['codSolicitud'];

        // Encabezado de la solicitud
        $sql = "SELECT COUNT(*) as cantidad
                FROM frm_factura_enca   
                WHERE cod_solicitud = $codSolicitud";       
        $result = $this->execute($sql);
        $cantidad = $this->getArray($result);

        echo json_encode(array('cantidad'=>$cantidad)); 
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
}

?>