<?php 

require ('BaseController.php');
class SolicitudController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function all() {

        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['SRG_SOLICITUD_VEHICULOS', 'alias'=>'sv'];

        $columns = [
            'cod_solicitud' => 'solicitud', 
            //Relation Fields
            "$nameFuncionario" => 'funcionario',
            'p.des_programa' => 'programa', 
            'rcc.des_centro' => 'centro', 
            'fec_solicitud' => 'Fec.Solicitud', 
            'ind_estado' => 'estado'
        ];

        $dataHorasInhabiles = array();

        // NO es ADMIN
        if ($codUsuario != 0) {

            //Excepcion: Se valida si es el Jefe de Unidad de Trasportes
            $codJefeUnidadTrasportes = $this->getJefeUnidadTransportes();
            $funcionariosACargo = $_SESSION['FUNCIONARIOS_A_CARGO'];
            if ($_SESSION['COD_FUNCIONARIO'] == $codJefeUnidadTrasportes) {
                $funcionariosACargo .= ", ".$this->getFuncionariosUnidadTrasportes();
            }

            //Exception: Si es gerente se elimina el filtro de funcionarios a cargo.
            if ($_SESSION["TIPO_FUNCIONARIO"] == "GERENTE") {
                $conditions = [
                    'where' => [
                        ['ind_estado' => 'C']
                    ]
                ];
            } else {
                $conditions = [
                    'where' => [
                        ['ind_estado' => 'C'],
                        ['in' => 
                            ['cod_funcionario' => $funcionariosACargo]
                        ]
                    ]
                ];                
            }


            if ($_SESSION["TIPO_FUNCIONARIO"] == "JEFE" || 
                $_SESSION["TIPO_FUNCIONARIO"] == "DIR. FINANCIERO" || 
                $_SESSION["TIPO_FUNCIONARIO"] == "JEFE SERVICIOS GENERALES") {

                $special_condition = ['special_condition' => 
                                         ['CONVERT(CHAR(8), sv.fec_salida, 112) = CONVERT(CHAR(8), sv.fec_ingreso, 112)'],
                                         ['DATEPART(HOUR, sv.fec_salida) >= 8'],
                                         ['DATEPART(HOUR, sv.fec_salida) <= 16']
                                     ];

                array_push($conditions['where'], $special_condition);

            }


        } else { // ES ADMIN
            $conditions = [
                'where' => [
                    ['ind_estado' => 'C']
                ]
            ];
        }

        $relations = [
            'join' => [
                array ('RH_FUNCIONARIOS', //table
                       'cod_funcionario', //columns
                       'ssf' //alias
                ),
                array ('RH_CENTROS_COSTO', //table
                       'cod_centro', //column
                       'rcc' //alias
                ),
                array ('PRE_PROGRAMAS', //table
                       'cod_programa', //column
                       'p' //alias
                ),
            ]
        ];

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $sqlString = $this->getQueryString();
        $solicitudes = $this->getArray($result);

        // NO es ADMIN
        if ($codUsuario != 0) {
            if ($_SESSION["TIPO_FUNCIONARIO"] == "DIR. FINANCIERO" || 
                $_SESSION["TIPO_FUNCIONARIO"] == "JEFE SERVICIOS GENERALES") {

                $dataHorasInhabiles = $this->getSolicitudesHorasInhabiles($sqlString);
                foreach ($dataHorasInhabiles['solicitudes'] as $row) {
                    array_push($solicitudes, $row);
                }

            }

        }

        $data_result = array('columns'=>$columns, 
                             'solicitudes'=>$solicitudes, 
                             'sql' => $sqlString,
                             'dataHorasInhabiles' => $dataHorasInhabiles
                            );

        echo json_encode($data_result);
    }

    /**
     * Funciom que se encarga de obtener el cod_funcionario del Jefe de Unidad de transportes
     * @return cod_funcionario
    */
    private function getJefeUnidadTransportes() {
        
        $sql = "SELECT val_dato as codFuncionario FROM SIF_CONFIGURADORES WHERE cod_configurador = 28;";
        $result = $this->execute($sql);
        $data = $this->getArray($result);

        return $data[0]['codFuncionario'];

    }

    private function getFuncionariosUnidadTrasportes(){
        $sql = "SELECT val_dato as codFuncionario FROM SIF_CONFIGURADORES WHERE cod_configurador in (52, 53);";
        $result = $this->execute($sql);
        $data = $this->getArray($result);

        $str_list = "";
        foreach ($data as $funcionario) {
            $str_list .= $funcionario['codFuncionario']. ", ";
        }

        $str_list = substr_replace($str_list, "", -2);

        return $str_list;
    }

    private function replaceQueryString($query) {

        
        $start = "SELECT";
        $end = "FROM";
        
        $replacement = " sv.cod_solicitud ";

        $r = explode($start, $query);
        if (isset($r[1])){
            $r = explode($end, $r[1]);            
            $newText = str_replace(array($r[0]), $replacement, $query);
            return $newText;
        }
        return '';

    }

    private function getSolicitudesHorasInhabiles($sql=null) {

        $sqlNotIn = $this->replaceQueryString($sql);

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['SRG_SOLICITUD_VEHICULOS', 'alias'=>'sv2'];

        $columns = [
            'cod_solicitud' => 'solicitud', 
            //Relation Fields
            "$nameFuncionario" => 'funcionario',
            'p.des_programa' => 'programa', 
            'rcc.des_centro' => 'centro', 
            'fec_solicitud' => 'Fec.Solicitud', 
            'ind_estado' => 'estado'
        ];


        $conditions = [
            'where' => [
                ['ind_estado' => 'C'],
                ['special_condition' => 
                    ["sv2.cod_solicitud not in ($sqlNotIn)"]
                ]                
            ]
        ];   

        $relations = [
            'join' => [
                array ('RH_FUNCIONARIOS', //table
                       'cod_funcionario', //columns
                       'ssf' //alias
                ),
                array ('RH_CENTROS_COSTO', //table
                       'cod_centro', //column
                       'rcc' //alias
                ),
                array ('PRE_PROGRAMAS', //table
                       'cod_programa', //column
                       'p' //alias
                ),
            ]
        ];

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $sqlString = $this->getQueryString();
        $solicitudes = $this->getArray($result);

        $data_result = array('columns'=>$columns, 
                             'solicitudes'=>$solicitudes, 
                             'sql' => $sqlString
                            );

        return $data_result;

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
        $id = intval($params["idSolicitud"]);

        // Encabezado de la solicitud
        $sql = "SELECT sv.*, 
                       CONCAT (f.des_nombre, SPACE(1), f.des_apellido1, SPACE(1), f.des_apellido2) as funcionario, 
                       c.des_centro as centro, 
                       p.des_programa as programa, 
                       sifP.des_provincia as provincia, 
                       sifC.des_canton as canton, 
                       sifD.des_distrito as distrito 
                FROM SRG_SOLICITUD_VEHICULOS as sv, 
                     RH_FUNCIONARIOS as f, 
                     RH_CENTROS_COSTO as c, 
                     PRE_PROGRAMAS as p,
                     SIF_PROVINCIAS as sifP,
                     SIF_CANTONES as sifC,
                     SIF_DISTRITOS as sifD 
                WHERE sv.cod_solicitud     = $id 
                    AND sv.cod_funcionario = f.cod_funcionario 
                    AND sv.cod_centro      = c.cod_centro 
                    AND sv.cod_programa    = p.cod_programa 
                    AND sv.cod_prov = sifP.cod_provincia 
                    AND sv.cod_cant = sifC.cod_canton
                    AND sv.cod_dist = sifD.cod_distrito";
        $result = $this->execute($sql);
        $solicitud = $this->getArray($result);

        // Funcionarios de la solicitud
        $sql = "SELECT sf.*
                FROM SRG_SOLICITUD_FUNCIONARIOS as sf 
                WHERE cod_solicitud = $id";
        $result = $this->execute($sql);
        $funcionarios = $this->getArray($result);

        echo json_encode(array('solicitud'=>$solicitud, 'funcionarios' => $funcionarios));
    }

    public function approveSolicitud(){
        $params = $this->getParameters();
        $id = intval($params["codSolicitud"]);

        $sql = "UPDATE SRG_SOLICITUD_VEHICULOS set ind_estado = 'A' WHERE cod_solicitud = $id";
        $result = $this->execute($sql);

        if ($result) {
            echo json_encode(array('response'=>1));
        } else {
            echo json_encode(array('response'=>0));
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

    public function update() {
        $params = $this->getParameters();

        // Update Encabezado
        //========================================      
        $encabezado = json_decode($params['encabezado']);
        $codSolicitud   = $encabezado->cod_solicitud;
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
            $columnTipoCambio = "mon_tipo_cambio = $tipoCambio, ";
            $columnMonSaldoMonex = ", mon_saldo_monex";
        }       

        if ($codProveedor != 0) {           
            $setProveedor = "cod_proveedor = $codProveedor, ";
        } else {
            $setProveedor = "cod_proveedor = NULL, ";           
        }

        $qEncabezado = "UPDATE frm_solic_pedido_enca 
                        SET cod_periodo = $codPeriodo, 
                            cod_programa = $codPrograma, 
                            $setProveedor 
                            fec_registro = GETDATE(), 
                            des_observacion = '$desObservacion', 
                            ind_estado = $indEstado, 
                            mon_total = $monTotal, 
                            ind_moneda = $indMoneda,
                            mon_saldo = $monSaldo,
                            $columnTipoCambio
                            mon_total_monex = $monTotalMonex,
                            mon_saldo_monex = $monSaldoMonex  
                        WHERE cod_solicitud = $codSolicitud";

        $this->execute($qEncabezado);

        // Delete last registers
        $delete = "DELETE FROM frm_solic_pedido_deta WHERE cod_solicitud = $codSolicitud";
        $this->execute($delete);

        // Update Detalle
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
            $listValues .= "($codSolicitud, $nLine, $codCuenta, '$descripcion', $cantidad, $preUnit, $totLine, $monDetalleMonex, $totLine $monSaldoMonex), "; 

            $nLine++;
        }

        $listValues = substr_replace($listValues, ";", -2);
        $qDetalle .= $listValues;

        $this->execute($qDetalle);

        echo json_encode(array('query'=>$qDetalle));
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