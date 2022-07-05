<?php 

require ('BaseController.php');
class ViaticoController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function getAllAdelantoViaticos() {

        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecAdelanto = 'fecAdelanto';
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecAdelanto = 'CONVERT(VARCHAR(33), fec_adelanto, 126)';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['TES_CCHV_ADELANTO_ENC', 'alias'=>'v'];

        $columns = [
            'num_adelanto' =>'solicitud',                        
            'tcc.des_clasificacion' =>'clasificacion',
            "$fecAdelanto" =>'fecha',
            "$nameSolicitante" => 'solicitante',
            'mon_adelanto' =>'monto',
            'SUBSTRING(tce.des_estado, 1, 1)' =>'estado'
        ];

        // NO es ADMIN
        if ($codUsuario != 0) {
            $conditions = [
                //'limit' => 10,
                'where' => [
                    ['cod_estado' => '1'],
                    ['in' => 
                        ['cod_solicitante' => $_SESSION['FUNCIONARIOS_A_CARGO']]
                    ]               
                ]
            ];
        } else {
            $conditions = [
                'where' => [
                    ['cod_estado' => '1']
                ]
            ];
        }

        $relations = [
            'join' => [
                array ('RH_FUNCIONARIOS', //table
                       array('cod_solicitante'=>'cod_funcionario'), //columns
                       'ssf' //alias
                ),
                array ('TES_CCH_ESTADOS', //table
                       'cod_estado', //column
                       'tce' //alias
                ),
                array ('TES_CCH_CLASIFICACION', //table
                       'cod_clasificacion', //column
                       'tcc' //alias
                )
            ]
        ];

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $query = $this->getQueryString();
        $solicitudes = $this->getArray($result);

        echo json_encode(array('columns'=>$columns,'adelantoViaticos'=>$solicitudes, 'query' => $query));
    }

    public function getAllLiquidacionViaticos() {

        session_start();

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecComprobante = 'fec_comprobante';
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecComprobante = 'CONVERT(VARCHAR(33), fec_comprobante, 126)';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['TES_CCHV_COMPROBANTE_ENCABEZADO', 'alias'=>'v'];

       //cod_centro_costo, des_motivo, des_observaciones, 
       //cod_presupuesto, cod_autoriza, fec_autorizacion, cod_clasificacion, cod_estado, 
       //cod_anula, fec_anulacion, des_justificacion, cod_entrega, fec_entrega, num_adelanto, Estado, COD_META,
       //ind_transferencia, NUM_TRANSFERENCIA
        $columns = [
            'num_comprobante' =>'solicitud',                        
            'tcc.des_clasificacion' =>'clasificacion',
            "$fecComprobante" => 'fecha',
            "$nameSolicitante" => 'solicitante',
            'mon_comprobante' =>'monto',
            'SUBSTRING(tce.des_estado, 1, 1)' =>'estado'
        ];

        // NO es ADMIN
        if ($codUsuario != 0) {
            $conditions = [
                //'limit' => 10,
                'where' => [
                    ['cod_estado' => '1'],
                    ['in' => 
                        ['cod_solicitante' => $_SESSION['FUNCIONARIOS_A_CARGO']]
                    ]               
                ]
            ];
        } else {
            $conditions = [
                'where' => [
                    ['cod_estado' => '1']
                ]
            ];
        }

        $relations = [
            'join' => [
                array ('RH_FUNCIONARIOS', //table
                       array('cod_solicitante'=>'cod_funcionario'), //columns
                       'ssf' //alias
                ),
                array ('TES_CCH_ESTADOS', //table
                       'cod_estado', //column
                       'tce' //alias
                ),
                array ('TES_CCH_CLASIFICACION', //table
                       'cod_clasificacion', //column
                       'tcc' //alias
                )
            ]
        ];

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $query = $this->getQueryString();
        $solicitudes = $this->getArray($result);


        if ($connectionType == "odbc_mssql") {
            $solicitudes = $this->toUtf8($solicitudes);
        }          
        
        if ($connectionType == "odbc_mssql") {
          echo json_encode(array('columns'=>$columns,'liquidacionViaticos'=>$solicitudes, 'query' => $query), JSON_UNESCAPED_UNICODE);
        }else {          
          echo json_encode(array('columns'=>$columns,'liquidacionViaticos'=>$solicitudes, 'query' => $query));
        }

    }

    public function getNumAdelanto(){

        $params = $this->getParameters();
        $numAdelanto = $params["numAdelanto"];

        session_start();
        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecAdelanto = 'tcae.fec_adelanto';
        $fecDetalle = 'tcad.fec_detalle';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecAdelanto = 'CONVERT(VARCHAR(33), tcae.fec_adelanto, 126) as fec_adelanto';
            $fecDetalle = 'CONVERT(VARCHAR(33), tcad.fec_detalle, 126) as fec_detalle';
        }        

        $sql = "SELECT tcae.num_adelanto,
                       $fecAdelanto,
                       $nameFuncionario as solicitante,
                       tcae.cod_centro_costo,
                       cc.des_centro as centro,
                       tcae.des_motivo,
                       tcae.des_observaciones,
                       tcae.cod_presupuesto,
                       tcae.cod_autoriza,
                       tcae.fec_autorizacion,
                       tcae.mon_adelanto,
                       tcc.des_clasificacion as clasificacion,
                       SUBSTRING(tce.des_estado, 1, 1) as estado,
                       tcae.cod_meta,
                       tcae.num_transferencia 
                FROM TES_CCHV_ADELANTO_ENC as tcae, 
                     RH_FUNCIONARIOS as ssf, 
                     RH_CENTROS_COSTO as cc,
                     TES_CCH_CLASIFICACION as tcc,
                     TES_CCH_ESTADOS as tce 
                WHERE tcae.num_adelanto = $numAdelanto 
                AND tcae.cod_estado = 1 
                AND tcae.cod_estado = tce.cod_estado 
                AND tcae.cod_solicitante = ssf.cod_funcionario 
                AND tcae.cod_clasificacion = tcc.cod_clasificacion";
        $result    = $this->execute($sql);
        $solicitudEncabezado = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $solicitudEncabezado = $this->toUtf8($solicitudEncabezado);          
        }  

        $sqlD = "SELECT tcad.num_adelanto,
                       tcad.num_linea,
                       $fecDetalle,
                       tcad.cod_localidad,
                       tcad.hor_salida,
                       tcad.hor_regreso,
                       tcad.mon_desayuno,
                       tcad.mon_almuerzo,
                       tcad.mon_cena,
                       tcad.mon_estadia,
                       tcad.ind_salida,
                       tcad.ind_regreso 
                FROM TES_CCHV_ADELANTO_DETALLE as tcad 
                WHERE tcad.num_adelanto = $numAdelanto";

        $resultD    = $this->execute($sqlD);
        $solicitudDetalle = $this->getArray($resultD);

        if ($connectionType == "odbc_mssql") {
            $solicitudDetalle = $this->toUtf8($solicitudDetalle);          
        }

        $totalesDetalle = array();

        $totMonAlmuerzo = 0;
        $totMonCena     = 0;
        $totMonDesayuno = 0;
        $totMonEstadia  = 0;
        foreach ($solicitudDetalle as $lD) {
            $totMonAlmuerzo += floatval($lD['mon_almuerzo']);
            $totMonCena     += floatval($lD['mon_cena']);
            $totMonDesayuno += floatval($lD['mon_desayuno']);
            $totMonEstadia  += floatval($lD['mon_estadia']);
        }

        $totalesDetalle['totMonAlmuerzo'] = $totMonAlmuerzo; 
        $totalesDetalle['totMonCena']     = $totMonCena; 
        $totalesDetalle['totMonDesayuno'] = $totMonDesayuno; 
        $totalesDetalle['totMonEstadia']  = $totMonEstadia; 


        if ($connectionType == "odbc_mssql") {
            echo json_encode(array('encabezado'=>$solicitudEncabezado, 'detalle' => $solicitudDetalle, 'totales' => $totalesDetalle), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('encabezado'=>$solicitudEncabezado, 'detalle' => $solicitudDetalle, 'totales' => $totalesDetalle));
        }

    }

    public function getNumComprobante(){

        $params = $this->getParameters();
        $numComprobante = $params["numComprobante"];

        session_start();
        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecComprobante = 'tcce.fec_comprobante';
        $fecDetalle = 'tcd.fec_detalle';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecComprobante = 'CONVERT(VARCHAR(33), tcce.fec_comprobante, 126) as fec_comprobante';
            $fecDetalle = 'CONVERT(VARCHAR(33), tcd.fec_detalle, 126) as fec_detalle';
        }        

        $sql = "SELECT tcce.num_comprobante, 
                       $fecComprobante, 
                       $nameFuncionario as solicitante,
                       tcce.cod_centro_costo, 
                       cc.des_centro as centro, 
                       tcce.des_motivo, 
                       tcce.des_observaciones, 
                       tcce.cod_presupuesto, 
                       tcce.cod_autoriza, 
                       tcce.fec_autorizacion, 
                       tcce.mon_comprobante, 
                       tcc.des_clasificacion as clasificacion, 
                       SUBSTRING(tce.des_estado, 1, 1) as estado,
                       tcce.cod_anula, 
                       tcce.fec_anulacion, 
                       tcce.des_justificacion, 
                       tcce.cod_entrega, 
                       tcce.fec_entrega, 
                       tcce.num_adelanto, 
                       tcce.cod_meta, 
                       tcce.num_transferencia, 
                       tcce.cod_comp_ingresos, 
                       tcce.cod_comp_cajach, 
                       tcce.mon_funcionario, 
                       tcce.mon_devolver, 
                       tcce.ind_transferencia,  
                       tcce.num_transferencia_liq 
                FROM TES_CCHV_COMPROBANTE_ENCABEZADO as tcce, 
                     RH_FUNCIONARIOS as ssf,
                     RH_CENTROS_COSTO as cc,
                     TES_CCH_CLASIFICACION as tcc, 
                     TES_CCH_ESTADOS as tce 
                WHERE tcce.num_comprobante = $numComprobante 
                    AND tcce.cod_estado = 1 
                    AND tcce.cod_centro_costo = cc.cod_centro
                    AND tcce.cod_estado = tce.cod_estado 
                    AND tcce.cod_solicitante = ssf.cod_funcionario 
                    AND tcce.cod_clasificacion = tcc.cod_clasificacion";

        $result = $this->execute($sql);
        $solicitudEncabezado = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $solicitudEncabezado = $this->toUtf8($solicitudEncabezado);          
        }  

        $sqlD = "SELECT tcd.num_comprobante, 
                        tcd.num_linea, 
                        $fecDetalle, 
                        tcd.cod_localidad, 
                        tcd.hor_salida, 
                        tcd.hor_regreso, 
                        tcd.mon_desayuno, 
                        tcd.mon_almuerzo, 
                        tcd.mon_cena, 
                        tcd.mon_estadia 
                FROM TES_CCHV_COMPROBANTE_DETALLE as tcd 
                WHERE tcd.num_comprobante = $numComprobante";

        $resultD    = $this->execute($sqlD);
        $solicitudDetalle = $this->getArray($resultD);

        if ($connectionType == "odbc_mssql") {
            $solicitudDetalle = $this->toUtf8($solicitudDetalle);          
        }

        $totalesDetalle = array();

        $totMonAlmuerzo = 0;
        $totMonCena     = 0;
        $totMonDesayuno = 0;
        $totMonEstadia  = 0;
        foreach ($solicitudDetalle as $lD) {
            $totMonAlmuerzo += floatval($lD['mon_almuerzo']);
            $totMonCena     += floatval($lD['mon_cena']);
            $totMonDesayuno += floatval($lD['mon_desayuno']);
            $totMonEstadia  += floatval($lD['mon_estadia']);
        }

        $totalesDetalle['totMonAlmuerzo'] = $totMonAlmuerzo; 
        $totalesDetalle['totMonCena']     = $totMonCena; 
        $totalesDetalle['totMonDesayuno'] = $totMonDesayuno; 
        $totalesDetalle['totMonEstadia']  = $totMonEstadia;

        if ($connectionType == "odbc_mssql") {
            echo json_encode(array('encabezado'=>$solicitudEncabezado, 'detalle' => $solicitudDetalle, 'totales' => $totalesDetalle), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('encabezado'=>$solicitudEncabezado, 'detalle' => $solicitudDetalle, 'totales' => $totalesDetalle));
        }

    }

    public function approveSolicitud() {
        session_start();
        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();
        $id        = intval($params["codSolicitud"]);
        $codCentro = trim($params["codCentro"]);
        $codMeta   = $params["codMeta"];
        $montoAdelanto     = number_format(floatval($params["monto"]), 2, '.', '');

        //Se obtienen los configuradores requeridos para la aprobacion
        $configuradores = $this->getConfiguradores();
        $yearPresupuesto = $configuradores[0]['VAL_DATO'];
        $codSubPartida   = $configuradores[1]['VAL_DATO'];

        //Se obtienen los montos provisionales y disponibles actuales 
        $montos = $this->getMontosActuales($yearPresupuesto, $codCentro, $codSubPartida, $codMeta);
        $provisional = number_format(floatval($montos[0]['provisional']), 2, '.', '');
        $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');

        $new_provisional = $provisional + $montoAdelanto;
        $new_disponible  = $disponible - $montoAdelanto;

        $f_provisional = number_format(floatval($new_provisional), 2, '.', '');
        $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');

        $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                SET mon_compromiso_provisional = $f_provisional, 
                    mon_disponible = $f_disponible 
                WHERE ano_presupuesto = $yearPresupuesto 
                AND cod_centro = '$codCentro' 
                AND cod_subpartida = '$codSubPartida' 
                AND cod_meta = '$codMeta'";
        $resultA = $this->execute($sql);

        $status_process = 1;
        if ($resultA) {

            //Se obtiene la version
            $version    = $this->getVersion($yearPresupuesto);
            //Se genera la nueva linea de detalle
            $codDetalle = $this->getCodigoDetalle() + 1;

            $sql = "INSERT INTO PRE_EJECUCION_PRESUPUESTO_DETALL (ano_presupuesto, cod_version, cod_centro, cod_subpartida, 
                    cod_detalle, num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, COD_META) 
                    VALUES ($yearPresupuesto, $version, '$codCentro', '$codSubPartida', 
                    $codDetalle, $id, 10, $montoAdelanto, 'CP', NULL, $codMeta)";

            $resultB = $this->execute($sql);

            if ($resultB) {

                $sql = "UPDATE TES_CCHV_ADELANTO_ENC 
                            SET cod_estado = 2, 
                                fec_autorizacion = GETDATE(), 
                                cod_autoriza = $codUsuario 
                        WHERE num_adelanto = $id";
                $resultC = $this->execute($sql);

                if (!$resultC) {
                    $status_process = 0;
                }

            } else {
                $status_process = 0;
            }

        } else {
            $status_process = 0;
        }

        echo json_encode(array('response'=>$status_process));

    }

    public function approveLiquidacionViaticos() {

        session_start();
        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();

        //Numero de Comprobante
        $numComprobante   = intval($params["codSolicitud"]);

        // Encabezado del Comprobante
        $encabezado = $this->getEncabezadoComprobante($numComprobante);
        $montoComprobante = number_format(floatval($encabezado['mon_comprobante']), 2, '.', '');
        $numAdelanto      = intval($encabezado['num_adelanto']);
        $codCentro        = trim($encabezado['cod_centro_costo']);
        $codMeta          = $encabezado["cod_meta"];     

        //Se obtienen los configuradores requeridos para la aprobacion
        $configuradores  = $this->getConfiguradores();
        $yearPresupuesto = $configuradores[0]['VAL_DATO'];
        $codSubPartida   = $configuradores[1]['VAL_DATO'];

        //Se obtienen los montos provisionales y disponibles actuales 
        $montos = $this->getMontosActuales($yearPresupuesto, $codCentro, $codSubPartida, $codMeta);
        $provisional = number_format(floatval($montos[0]['provisional']), 2, '.', '');
        $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');
        $definitivo  = number_format(floatval($montos[0]['definitivo']), 2, '.', '');

        $status_process = 1;
        
        //Liquidacion tiene adelanto
        if ($numAdelanto > 0) {            
            $monAdelanto = number_format(floatval($this->getMonAdelanto($numAdelanto)), 2, '.', '');

            $statusP = true;
            if ($montoComprobante < $monAdelanto) {
                $diferencia = $monAdelanto - $montoComprobante;

                $new_provisional = $provisional - $montoAdelanto;
                $new_disponible  = $disponible + $diferencia;
                $new_definitivo  = $definitivo + $montoComprobante;

                $f_provisional = number_format(floatval($new_provisional), 2, '.', '');
                $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
                $f_definitivo  = number_format(floatval($new_definitivo), 2, '.', '');

                $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                        SET mon_compromiso_provisional = $f_provisional, 
                            mon_disponible = $f_disponible,
                            mon_compromiso_definitivo = $f_definitivo 
                        WHERE ano_presupuesto = $yearPresupuesto 
                        AND cod_centro = '$codCentro' 
                        AND cod_subpartida = '$codSubPartida' 
                        AND cod_meta = '$codMeta'";
                $resultA = $this->execute($sql);

                if (!$resultA) {
                    $statusP = false;
                    $status_process = 0;
                }

            } else if ($montoComprobante > $monAdelanto) {
                $diferencia = $montoComprobante - $monAdelanto;

                // Se autoriza
                if ($diferencia >= $disponible) {

                    $new_disponible  = $disponible - $diferencia;
                    $new_provisional = $provisional - $montoAdelanto;
                    $new_definitivo  = $definitivo + $montoComprobante;

                    $f_provisional = number_format(floatval($new_provisional), 2, '.', '');
                    $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
                    $f_definitivo  = number_format(floatval($new_definitivo), 2, '.', '');

                    $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                            SET mon_compromiso_provisional = $f_provisional, 
                                mon_disponible = $f_disponible,
                                mon_compromiso_definitivo = $f_definitivo 
                            WHERE ano_presupuesto = $yearPresupuesto 
                            AND cod_centro = '$codCentro' 
                            AND cod_subpartida = '$codSubPartida' 
                            AND cod_meta = '$codMeta'";
                    $resultA = $this->execute($sql);

                    if (!$resultA) {
                        $statusP = false;
                        $status_process = 0;
                    }

                // NO se autoriza
                } else {                    
                    // No se puede autorizar Y enviar mensaje al usuario No existe presupuesto disponible
                    $statusP = false;
                    $status_process = -1;
                }

            } else if ($montoComprobante == $monAdelanto) {
                $new_provisional = $provisional - $montoAdelanto;
                $new_definitivo  = $definitivo + $montoComprobante;

                $f_provisional = number_format(floatval($new_provisional), 2, '.', '');
                $f_definitivo  = number_format(floatval($new_definitivo), 2, '.', '');
                
                $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                        SET mon_compromiso_provisional = $f_provisional,                             
                            mon_compromiso_definitivo = $f_definitivo 
                        WHERE ano_presupuesto = $yearPresupuesto 
                        AND cod_centro = '$codCentro' 
                        AND cod_subpartida = '$codSubPartida' 
                        AND cod_meta = '$codMeta'";
                $resultA = $this->execute($sql);

                if (!$resultA) {
                    $statusP = false;
                    $status_process = 0;
                }

            }

            if ($statusP) {

                //Se obtiene la version
                $version    = $this->getVersion($yearPresupuesto);
                //Se genera la nueva linea de detalle
                $codDetalle = $this->getCodigoDetalle() + 1;

                $sql = "INSERT INTO PRE_EJECUCION_PRESUPUESTO_DETALL (ano_presupuesto, cod_version, cod_centro, cod_subpartida, 
                        cod_detalle, num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, COD_META) 
                        VALUES ($yearPresupuesto, $version, '$codCentro', '$codSubPartida', 
                        $codDetalle, $numComprobante, 11, $montoComprobante, 'CD', NULL, $codMeta)";

                $resultB = $this->execute($sql);

                if ($resultB) {

                    $sql = "DELETE FROM PRE_EJECUCION_PRESUPUESTO_DETALL 
                            WHERE num_adelanto = $numAdelanto 
                                AND tip_detalle = 'CP' 
                                AND tip_documento = 10 
                                AND ano_presupuesto = $yearPresupuesto 
                                AND cod_centro = '$codCentro' 
                                AND cod_subpartida = '$codSubPartida' 
                                AND COD_META = $codMeta";

                    $resultC = $this->execute($sql);

                    if ($resultC) {

                        $sql = "UPDATE TES_CCHV_COMPROBANTE_ENCABEZADO 
                                    SET cod_estado = 2, 
                                        fec_autorizacion = GETDATE(), 
                                        cod_autoriza = $codUsuario 
                                WHERE num_comprobante = $numComprobante";
                        $resultD = $this->execute($sql);

                        if (!$resultD) {
                            $status_process = 0;
                        }

                    } else {
                        $status_process = 0;
                    }

                } else {
                    $status_process = 0;
                }

            }


        // Liquidacion SIN adelanto
        } else {

            if ($montoComprobante <= $disponible) {

                $new_disponible  = $disponible - $montoComprobante;
                $new_definitivo  = $definitivo + $montoComprobante;

                $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
                $f_definitivo  = number_format(floatval($new_definitivo), 2, '.', '');

                $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                        SET mon_disponible = $f_disponible, 
                            mon_compromiso_definitivo = $f_definitivo 
                        WHERE ano_presupuesto = $yearPresupuesto 
                        AND cod_centro = '$codCentro' 
                        AND cod_subpartida = '$codSubPartida' 
                        AND cod_meta = '$codMeta'";

                $resultA = $this->execute($sql);

                if ($resultA) {

                    //Se obtiene la version
                    $version    = $this->getVersion($yearPresupuesto);
                    //Se genera la nueva linea de detalle
                    $codDetalle = $this->getCodigoDetalle() + 1;

                    $sql = "INSERT INTO PRE_EJECUCION_PRESUPUESTO_DETALL (ano_presupuesto, cod_version, cod_centro, cod_subpartida, 
                            cod_detalle, num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, COD_META) 
                            VALUES ($yearPresupuesto, $version, '$codCentro', '$codSubPartida', 
                            $codDetalle, $numComprobante, 11, $montoComprobante, 'CD', GETDATE(), $codMeta)";

                    $resultB = $this->execute($sql);
    
                    if ($resultB) {
                        $sql = "UPDATE TES_CCHV_COMPROBANTE_ENCABEZADO 
                                    SET cod_estado = 2, 
                                        fec_autorizacion = GETDATE(), 
                                        cod_autoriza = $codUsuario 
                                WHERE num_comprobante = $numComprobante";
                        $resultC = $this->execute($sql);

                        if (!$resultC) {
                            $status_process = 0;
                        }

                    } else {
                        $status_process = 0;
                    }
                } else {
                    $status_process = 0;
                }

            } else {
                // No se puede autorizar Y enviar mensaje al usuario No existe presupuesto disponible
                $status_process = -1;
            }

        }

        echo json_encode(array('response'=>$status_process));

    }

    private function getConfiguradores() {

        $sql = "SELECT VAL_DATO 
                FROM SIF_CONFIGURADORES
                WHERE COD_CONFIGURADOR in (13, 15)";

        $result = $this->execute($sql); 
        $configuradores = $this->getArray($result);

        return $configuradores;
    }

    private function getMontosActuales($yearPresupuesto, $codCentro, $codSubPartida, $codMeta) {

        $sql = "SELECT mon_compromiso_provisional as provisional,
                       mon_disponible as disponible, 
                       mon_compromiso_definitivo as definitivo  
                FROM pre_ejecucion_presupuesto_encabe 
                WHERE ano_presupuesto = $yearPresupuesto 
                    AND cod_centro = '$codCentro' 
                    AND cod_subpartida = '$codSubPartida'
                    AND cod_meta = '$codMeta'";
        $result = $this->execute($sql);
        $montos = $this->getArray($result);

        return $montos;
    }

    private function getVersion($yearPresupuesto) {

        $sql = "SELECT max(cod_version) as version 
                FROM PRE_PRESUPUESTO_ENCABEZADO
                WHERE ano_presupuesto = $yearPresupuesto";
        $result = $this->execute($sql);
        $version = $this->getArray($result);

        return $version[0]['version'];
    }

    private function getCodigoDetalle() {

        $sql = "SELECT max(cod_detalle) as codDetalle 
                FROM PRE_EJECUCION_PRESUPUESTO_DETALL";
        $result = $this->execute($sql);
        $codigo = $this->getArray($result);

        return $codigo[0]['codDetalle'];
    }

    private function getEncabezadoComprobante($numComprobante) {

        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        } 

        $sql = "SELECT tcce.num_comprobante, 
                       tcce.fec_comprobante, 
                       $nameFuncionario as solicitante,
                       tcce.cod_centro_costo, 
                       cc.des_centro as centro, 
                       tcce.des_motivo, 
                       tcce.des_observaciones, 
                       tcce.cod_presupuesto, 
                       tcce.cod_autoriza, 
                       tcce.fec_autorizacion, 
                       tcce.mon_comprobante, 
                       tcc.des_clasificacion as clasificacion, 
                       SUBSTRING(tce.des_estado, 1, 1) as estado,
                       tcce.cod_anula, 
                       tcce.fec_anulacion, 
                       tcce.des_justificacion, 
                       tcce.cod_entrega, 
                       tcce.fec_entrega, 
                       tcce.num_adelanto, 
                       tcce.cod_meta, 
                       tcce.num_transferencia, 
                       tcce.cod_comp_ingresos, 
                       tcce.cod_comp_cajach, 
                       tcce.mon_funcionario, 
                       tcce.mon_devolver, 
                       tcce.ind_transferencia,  
                       tcce.num_transferencia_liq 
                FROM TES_CCHV_COMPROBANTE_ENCABEZADO as tcce, 
                     RH_FUNCIONARIOS as ssf,
                     RH_CENTROS_COSTO as cc,
                     TES_CCH_CLASIFICACION as tcc, 
                     TES_CCH_ESTADOS as tce 
                WHERE tcce.num_comprobante = $numComprobante 
                    AND tcce.cod_estado = 1 
                    AND tcce.cod_centro_costo = cc.cod_centro
                    AND tcce.cod_estado = tce.cod_estado 
                    AND tcce.cod_solicitante = ssf.cod_funcionario 
                    AND tcce.cod_clasificacion = tcc.cod_clasificacion";

        $result = $this->execute($sql);
        $solicitudEncabezado = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $solicitudEncabezado = $this->toUtf8($solicitudEncabezado);          
        }

        return $solicitudEncabezado[0];
    }

    private function getMonAdelanto($numAdelanto) {

        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $sql = "SELECT tcae.mon_adelanto 
                FROM TES_CCHV_ADELANTO_ENC as tcae 
                WHERE tcae.num_adelanto = $numAdelanto";
        $result = $this->execute($sql);
        $data   = $this->getArray($result);

        if ($connectionType == "odbc_mssql") {
            $data = $this->toUtf8($data);          
        }

        return $data[0]['mon_adelanto'];
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