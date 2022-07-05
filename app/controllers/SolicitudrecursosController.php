<?php 

require ('BaseController.php');
class SolicitudrecursosController extends BaseController
{

    //private $currentYear = '2015';
    private $currentYear = '(SELECT VAL_DATO FROM SIF_CONFIGURADORES WHERE COD_CONFIGURADOR = 13)';

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function all() {

        session_start();

        $params = $this->getParameters();
        $indEstado = intval($params["indEstado"]);

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecSolicitud = "fec_solicitud";
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecSolicitud = 'CONVERT(VARCHAR(33), fec_solicitud, 126)';
        }   

        $columns = [
            'cod_solicitud' =>'número',            
            "$nameSolicitante" => 'solicitante',
            'rcc.des_centro' => 'centro costo', 
            'ind_tipo' =>'tipo',
            "$fecSolicitud" =>'fecha',
            "mon_solicitud" =>'monto',
            "ind_estado" =>'ind_estado',
            "pesm.des_estado" =>'estado'
        ];

        // Se obtienen las solicitudes que NO son de LEGAL (Gerencia y Jefatura)
        if ($indEstado != 12) {

            if($indEstado == '2') {
                $solicitudes = $this->getSolicitudesAprobaciones($indEstado, false);
            } else {
                $solicitudes = $this->getSolicitudesAprobaciones($indEstado);
            }
            
        } else {
            $solicitudes = $this->getSolicitudesAprobacionesLegal($indEstado);
        }

        if ($connectionType == "odbc_mssql") {
            $solicitudes = $this->toUtf8($solicitudes);
        }          
        
        if ($connectionType == "odbc_mssql") {
          echo json_encode(array('columns'=>$columns,'recursos'=>$solicitudes), JSON_UNESCAPED_UNICODE);
        }else {          
          echo json_encode(array('columns'=>$columns,'recursos'=>$solicitudes));
        }
    }

    private function getSolicitudesAprobacionesLegal($indEstado) {
        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecSolicitud = "psm.fec_solicitud";
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecSolicitud = 'CONVERT(VARCHAR(33), psm.fec_solicitud, 126)';
        }         
        $sql = "SELECT  
                    psm.cod_solicitud as 'número', 
                    $nameSolicitante as 'solicitante', 
                    rcc.des_centro as 'centro costo', 
                    psm.cod_tipo as 'tipo', 
                    $fecSolicitud as 'fecha', 
                    psm.mon_solicitud as 'monto', 
                    psm.ind_estado as 'ind_estado', 
                    pesm.des_estado as 'estado' 
                FROM 
                    PRO_SOLICITUD_MATERIALES as psm, 
                    RH_FUNCIONARIOS as ssf, 
                    RH_CENTROS_COSTO as rcc, 
                    PRO_ESTADO_SOLICITUD_MATERIALES as pesm 
                WHERE psm.cod_funcionario = ssf.cod_funcionario 
                    AND psm.cod_centro_costo = rcc.cod_centro 
                    AND psm.ind_estado = pesm.cod_estado 
                    AND psm.ind_estado = $indEstado 
                    AND psm.cod_tipo = 'C' 
                    AND YEAR(psm.fec_solicitud) = ".$this->currentYear." 
                UNION
                SELECT  
                    psm.cod_solicitud as 'número', 
                    $nameSolicitante as 'solicitante',
                    rcc.des_centro as 'centro costo', 
                    psm.cod_tipo as 'tipo', 
                    $fecSolicitud as 'fecha', 
                    psm.mon_solicitud as 'monto', 
                    psm.ind_estado as 'ind_estado', 
                    pesm.des_estado as 'estado' 
                FROM 
                    PRO_SOLICITUD_MATERIALES as psm, 
                    RH_FUNCIONARIOS as ssf, RH_CENTROS_COSTO as rcc, 
                    PRO_ESTADO_SOLICITUD_MATERIALES as pesm 
                WHERE psm.cod_funcionario = ssf.cod_funcionario 
                    AND psm.cod_centro_costo = rcc.cod_centro 
                    AND psm.ind_estado = pesm.cod_estado 
                    AND psm.ind_estado = $indEstado
                    AND psm.cod_tipo <> 'C' 
                    AND YEAR(psm.fec_solicitud) = ".$this->currentYear." 
                    AND psm.MON_SOLICITUD > ( SELECT CAST(SIF_CONFIGURADORES.val_dato as DECIMAL(18,2))  
                FROM SIF_CONFIGURADORES  WHERE SIF_CONFIGURADORES.cod_configurador =  34)";
        $result = $this->execute($sql);
        $data = $this->getArray($result);

        return $data;
    }

    private function getSolicitudesAprobaciones($indEstado, $isJefatura = true) {
        $filterWithFuncionarios = "";
        if ($isJefatura){
            $filterWithFuncionarios = "AND psm.cod_funcionario in (".$_SESSION['FUNCIONARIOS_A_CARGO'].") ";
        }
        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $fecSolicitud = "psm.fec_solicitud";
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $fecSolicitud = 'CONVERT(VARCHAR(33), psm.fec_solicitud, 126)';
        }         
        $sql = "SELECT  
                    psm.cod_solicitud as 'número', 
                    $nameSolicitante as 'solicitante', 
                    rcc.des_centro as 'centro costo', 
                    psm.cod_tipo as 'tipo', 
                    $fecSolicitud as 'fecha', 
                    psm.mon_solicitud as 'monto', 
                    psm.ind_estado as 'ind_estado', 
                    pesm.des_estado as 'estado' 
                FROM 
                    PRO_SOLICITUD_MATERIALES as psm, 
                    RH_FUNCIONARIOS as ssf, 
                    RH_CENTROS_COSTO as rcc, 
                    PRO_ESTADO_SOLICITUD_MATERIALES as pesm 
                WHERE psm.cod_funcionario = ssf.cod_funcionario 
                    AND psm.cod_centro_costo = rcc.cod_centro 
                    AND psm.ind_estado = pesm.cod_estado 
                    AND psm.ind_estado = $indEstado 
                    $filterWithFuncionarios
                    AND YEAR(psm.fec_solicitud) = ".$this->currentYear;

        $result = $this->execute($sql);
        $data = $this->getArray($result);

        return $data;
    }

    public function get() {
        session_start();
        $params = $this->getParameters();
        $id = intval($params["idSolicitud"]);

        $connectionType = $_SESSION["CONNECTION_TYPE"];
        $nameSolicitante = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        $nameRecibe = 'CONCAT (rhf.des_nombre, SPACE(1),rhf.des_apellido1, SPACE(1), rhf.des_apellido2)';
        $nameAutoriza = 'CONCAT (rhf.des_nombre, SPACE(1),rhf.des_apellido1, SPACE(1), rhf.des_apellido2)';
        $nameResponsable = 'CONCAT (rhf.des_nombre, SPACE(1),rhf.des_apellido1, SPACE(1), rhf.des_apellido2)';
        $fecSolicitud = "psm.fec_solicitud";
        $fecRecibe = "psm.fec_recibe";
        $fecAutorizacion = "psm.fec_autorizacion";
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
            $nameRecibe = 'rhf.des_nombre + \' \' + rhf.des_apellido1 + \' \' + rhf.des_apellido2';
            $nameAutoriza = 'rhf.des_nombre + \' \' + rhf.des_apellido1 + \' \' + rhf.des_apellido2';
            $nameResponsable = 'rhf.des_nombre + \' \' + rhf.des_apellido1 + \' \' + rhf.des_apellido2';
            $fecSolicitud = 'CONVERT(VARCHAR(33), psm.fec_solicitud, 126)';
            $fecRecibe = 'CONVERT(VARCHAR(33), psm.fec_recibe, 126)';
            $fecAutorizacion = 'CONVERT(VARCHAR(33), psm.fec_autorizacion, 126)';
        }         
        $sql = "SELECT  
                    psm.cod_solicitud as 'cod_solicitud', 
                    $nameSolicitante as 'solicitante', 
                    (SELECT 
                        $nameRecibe 
                     FROM 
                        RH_FUNCIONARIOS as rhf 
                     WHERE 
                        rhf.cod_funcionario = psm.cod_recibe) as 'recibido_por', 
                    (SELECT 
                        $nameRecibe 
                     FROM 
                        RH_FUNCIONARIOS as rhf 
                     WHERE 
                        rhf.cod_funcionario = psm.cod_autoriza) as 'autorizado_por',  
                    (SELECT 
                        $nameResponsable 
                     FROM 
                        RH_FUNCIONARIOS as rhf 
                     WHERE 
                        rhf.cod_funcionario = psm.cod_responsable) as 'responsable', 
                    p.des_programa,
                    rcc.des_centro as 'centro_costo', 
                    psm.ind_tipo as 'tipo', 
                    $fecSolicitud as 'fec_solicitud', 
                    $fecRecibe as 'fec_recibe', 
                    $fecAutorizacion as 'fec_autorizacion', 
                    cod_categoria, 
                    psm.mon_solicitud as 'mon_solicitud', 
                    psm.ind_estado as 'ind_estado', 
                    psm.des_observaciones, 
                    petf.des_objetivo, 
                    petf.des_caracteristicas, 
                    petf.des_caracteristicas2, 
                    pesm.des_estado as 'des_estado' 
                FROM 
                    PRO_SOLICITUD_MATERIALES as psm 
                    LEFT OUTER JOIN PRO_ENCABEZADO_TERMINOS_REF as petf
                    ON psm.COD_SOLICITUD = petf.COD_SOLICITUD, 
                    RH_FUNCIONARIOS as ssf, 
                    PRE_PROGRAMAS as p, 
                    RH_CENTROS_COSTO as rcc, 
                    PRO_ESTADO_SOLICITUD_MATERIALES as pesm            
                WHERE psm.cod_funcionario = ssf.cod_funcionario 
                    AND psm.cod_centro_costo = rcc.cod_centro 
                    AND psm.ind_estado = pesm.cod_estado 
                    AND rcc.cod_programa = p.cod_programa                     
                    AND psm.cod_solicitud = $id";              

        $result = $this->execute($sql);
        $solicitud = $this->getArray($result); 

        $sql2 = "SELECT 
                    pdsm.cod_producto, 
                    pdsm.can_producto, 
                    RTRIM(pum.des_abreviacion) as des_abreviacion, 
                    pdsm.des_producto,
                    pdsm.mon_precio,
                    (pdsm.can_producto * pdsm.mon_precio) as mont_total,
                    pdsm.cod_cuenta_presupuesto_sug as cuenta_sugerida,
                    pm.num_meta as meta, 
                    pdsm.cod_cuenta_presupuesto_afec as afectacion_presupuestaria_real 
                FROM 
                    PRO_DETALLE_SOLICITUD_MATERIALES as pdsm, 
                    PRO_UNIDADES_MEDIDA as pum,
                    PLA_METAS as pm 
                WHERE pdsm.cod_solicitud = $id 
                    AND pdsm.cod_unidad = pum.cod_medida 
                    AND pdsm.cod_meta = pm.cod_meta";

        $result2 = $this->execute($sql2);
        $detalle = $this->getArray($result2);

        echo json_encode(array('solicitud'=>$solicitud, 'detalle' => $detalle), JSON_UNESCAPED_UNICODE);
    }

    public function approveSolicitud(){

        session_start();

        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();
        $numSolicitud = intval($params["numSolicitud"]);
        $type = $params["type"];

        $indEstado = 12;
        $fields = "";

        // Afectación presupuestaria
        $isAfectacionPresupuesto = false;

        // Autorización Jefatura
        if ($type === 'aj') {
            $indEstado = 2;
            $fields = "FEC_AUTORIZACION = GETDATE(), COD_AUTORIZA = $codUsuario ";
            $isAfectacionPresupuesto = true;
        }

        // Autorización Gerencia
        if ($type ===  'ag'){
            $indEstado = 12;
            $fields = "FEC_GERENCIA = GETDATE(), COD_GERENCIA = $codUsuario ";
        }

        // Autorización Legal
        if ($type === 'al') {
            $indEstado = 11;
            $fields = "FEC_COMISION = GETDATE(), COD_COMISION = $codUsuario ";
        }

        // echo json_encode(array('response'=>[$numSolicitud, $type, $indEstado]));

        if ($isAfectacionPresupuesto) {            

            //Se obtienen el configurador del año presupuestario vigente
            $configuradores  = $this->getConfiguradores();

            // CentroCosto
            $centroCosto = $this->getCentroCosto($numSolicitud);
            $yearPresupuesto = $configuradores[0]['VAL_DATO'];

            $detalleSolicitud = $this->getDetalleSolicitud($numSolicitud);

            $dataGrouped = $this->array_group_by($detalleSolicitud, ['COD_PARTIDA', 'COD_META'], ['MON_TOTAL']);

            $errorMessage = "Para la Partida XXX y la Meta XXX NO existe Disponible";
            $statusVerificacion = true;

            //Se obtiene la version
            $version    = $this->getVersion($yearPresupuesto);            
            
            foreach ($dataGrouped as $key => $line) {

                $codPartida = $line['COD_PARTIDA'];
                $codMeta = $line['COD_META'];
                $monTotal = $line['MON_TOTAL'];

                $montos = $this->getMontosActuales($yearPresupuesto, $centroCosto, $codPartida, $codMeta);
                $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');

                if ($monTotal > $disponible) {
                    $status_process = -1;
                    $statusVerificacion = false;
                }
            }    

            if ($statusVerificacion) {

                $finalStatus = true;
                foreach ($dataGrouped as $key => $line) {

                    $codPartida = $line['COD_PARTIDA'];
                    $codMeta = $line['COD_META'];
                    $monTotal = $line['MON_TOTAL'];

                    $montos = $this->getMontosActuales($yearPresupuesto, $centroCosto, $codPartida, $codMeta);
                    $provisional = number_format(floatval($montos[0]['provisional']), 2, '.', '');
                    $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');                    

                    if ($monTotal <= $disponible) {
                        
                        $new_provisional  = $provisional + $monTotal;
                        $new_disponible  = $disponible - $monTotal;

                        $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
                        $f_provisional = number_format(floatval($new_provisional), 2, '.', '');

                        $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                                SET mon_disponible = $f_disponible, 
                                    mon_compromiso_provisional = $f_provisional 
                                WHERE ano_presupuesto = $yearPresupuesto 
                                AND cod_centro = '$centroCosto' 
                                AND cod_subpartida = '$codPartida' 
                                AND cod_meta = '$codMeta'";

                        $resultA = $this->execute($sql);

                        if ($resultA) {

                            //Se genera la nueva linea de detalle
                            $codDetalle = $this->getCodigoDetalle() + 1;

                            $sql = "INSERT INTO PRE_EJECUCION_PRESUPUESTO_DETALL (ano_presupuesto, cod_version, cod_centro, cod_subpartida, 
                                    cod_detalle, num_documento, tip_documento, mon_gasto, tip_detalle, fec_liquidacion, COD_META) 
                                    VALUES ($yearPresupuesto, $version, '$centroCosto', '$codPartida', 
                                    $codDetalle, $numSolicitud, 1, $monTotal, 'CP', GETDATE(), $codMeta)";

                            $resultB = $this->execute($sql);

                            if (!$resultB) {
                                $query = "ROLLBACK;";
                                $this->execute($query);

                                $finalStatus = false;
                                $errorMessage = "";
                                $status_process = 0;
                            }


                        } else {
                            $query = "ROLLBACK;";
                            $this->execute($query);

                            $finalStatus = false;
                            $errorMessage = "";
                            $status_process = 0;
                        }

                    } else {
                        $status_process = -1;
                        $statusVerificacion = false;
                    }
                
                }

                if($finalStatus) {
                    $sql = "UPDATE pro_solicitud_materiales 
                                SET ind_estado = $indEstado, 
                                    $fields 
                            WHERE COD_SOLICITUD = $numSolicitud";
                    $resultC = $this->execute($sql);   

                    if (!$resultC) {
                        $status_process = 0;
                    }    

                    if ($resultC) {
                        echo json_encode(array('response'=>1));
                    } else {
                        echo json_encode(array('response'=>0));
                    }  
                }                 

            } else {

            }

        } else {
            $sql = "UPDATE pro_solicitud_materiales 
                        SET ind_estado = $indEstado, 
                            $fields 
                    WHERE COD_SOLICITUD = $numSolicitud";
            $result = $this->execute($sql);        

            if ($result) {
                echo json_encode(array('response'=>1));
            } else {
                echo json_encode(array('response'=>0));
            }            
        }
    }

    public function deniedSolicitud(){

        session_start();

        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();
        $numSolicitud = intval($params["numSolicitud"]);
        $type = $params["type"];
        $motivo = $params["motivo"];

        $indEstado = 14;
        $fields = "";

        // Afectación presupuestaria
        $isAfectacionPresupuesto = false;

        // echo json_encode(array('response'=>[$numSolicitud, $type, $indEstado, $motivo]));

        //Se obtienen el configurador del año presupuestario vigente
        $configuradores  = $this->getConfiguradores();

        // CentroCosto
        $centroCosto = $this->getCentroCosto($numSolicitud);
        $yearPresupuesto = $configuradores[0]['VAL_DATO'];

        $detalleSolicitud = $this->getDetalleSolicitud($numSolicitud);

        $dataGrouped = $this->array_group_by($detalleSolicitud, ['COD_PARTIDA', 'COD_META'], ['MON_TOTAL']);

        $errorMessage = "Para la Partida XXX y la Meta XXX NO existe Disponible";            

        //Se obtiene la version
        $version    = $this->getVersion($yearPresupuesto);            

        $finalStatus = true;
        foreach ($dataGrouped as $key => $line) {

            $codPartida = $line['COD_PARTIDA'];
            $codMeta = $line['COD_META'];
            $monTotal = $line['MON_TOTAL'];

            $montos = $this->getMontosActuales($yearPresupuesto, $centroCosto, $codPartida, $codMeta);
            $provisional = number_format(floatval($montos[0]['provisional']), 2, '.', '');
            $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');                    

            $new_provisional  = $provisional - $monTotal;
            $new_disponible  = $disponible + $monTotal;

            $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
            $f_provisional = number_format(floatval($new_provisional), 2, '.', '');

            $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                    SET mon_disponible = $f_disponible, 
                        mon_compromiso_provisional = $f_provisional 
                    WHERE ano_presupuesto = $yearPresupuesto 
                    AND cod_centro = '$centroCosto' 
                    AND cod_subpartida = '$codPartida' 
                    AND cod_meta = '$codMeta'";

            $resultA = $this->execute($sql);

            if ($resultA) {

                $sql = "DELETE FROM PRE_EJECUCION_PRESUPUESTO_DETALL
                        WHERE ano_presupuesto = $yearPresupuesto 
                            AND cod_centro = '$centroCosto' 
                            AND cod_subpartida = '$codPartida' 
                            AND cod_meta = '$codMeta'
                            AND tip_documento = 1
                            AND num_documento = $numSolicitud";

                $resultB = $this->execute($sql);

                if (!$resultB) {
                    $query = "ROLLBACK;";
                    $this->execute($query);

                    $finalStatus = false;
                    $errorMessage = "";
                    $status_process = 0;
                }


            } else {
                $query = "ROLLBACK;";
                $this->execute($query);

                $finalStatus = false;
                $errorMessage = "";
                $status_process = 0;
            }

            
        }

        if($finalStatus) {
            $sql = "UPDATE pro_solicitud_materiales 
                        SET ind_estado = $indEstado 
                    WHERE COD_SOLICITUD = $numSolicitud";
            $resultC = $this->execute($sql);   

            if (!$resultC) {
                $status_process = 0;
            }    

            if ($resultC) {

                //Se genera la nueva linea de detalle
                $codDenegado = $this->getCodigoDenegado() + 1;

                $sql = "INSERT INTO PRO_SOLICITUDES_DENEGADAS (cod_denegado, fec_denegado, cod_solicitud, cod_denega, des_justificacion)
                        VALUES ($codDenegado, GETDATE(), $numSolicitud, $codUsuario, '$motivo' )";
                $resultD = $this->execute($sql);   

                if ($resultD) {
                    echo json_encode(array('response'=>1));    
                } else {
                    echo json_encode(array('response'=>0));
                }
                
            } else {
                
            }  
        }
        
    }

    public function devolverSolicitud(){

        session_start();

        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();
        $numSolicitud = intval($params["numSolicitud"]);
        $type = $params["type"];
        $motivo = $params["motivo"];

        $indEstado = 13;
        $fields = "FEC_DEVOLUCION = GETDATE(), COD_DEVOLUCION = $codUsuario, DES_DEVOLUCION = '$motivo'";

        // Afectación presupuestaria
        $isAfectacionPresupuesto = false;

        // echo json_encode(array('response'=>[$numSolicitud, $type, $indEstado, $motivo]));

        //Se obtienen el configurador del año presupuestario vigente
        $configuradores  = $this->getConfiguradores();

        // CentroCosto
        $centroCosto = $this->getCentroCosto($numSolicitud);
        $yearPresupuesto = $configuradores[0]['VAL_DATO'];

        $detalleSolicitud = $this->getDetalleSolicitud($numSolicitud);

        $dataGrouped = $this->array_group_by($detalleSolicitud, ['COD_PARTIDA', 'COD_META'], ['MON_TOTAL']);

        $errorMessage = "Para la Partida XXX y la Meta XXX NO existe Disponible";            

        //Se obtiene la version
        $version    = $this->getVersion($yearPresupuesto);            

        $finalStatus = true;
        foreach ($dataGrouped as $key => $line) {

            $codPartida = $line['COD_PARTIDA'];
            $codMeta = $line['COD_META'];
            $monTotal = $line['MON_TOTAL'];

            $montos = $this->getMontosActuales($yearPresupuesto, $centroCosto, $codPartida, $codMeta);
            $provisional = number_format(floatval($montos[0]['provisional']), 2, '.', '');
            $disponible  = number_format(floatval($montos[0]['disponible']), 2, '.', '');                    

            $new_provisional  = $provisional - $monTotal;
            $new_disponible  = $disponible + $monTotal;

            $f_disponible  = number_format(floatval($new_disponible), 2, '.', '');
            $f_provisional = number_format(floatval($new_provisional), 2, '.', '');

            $sql = "UPDATE pre_ejecucion_presupuesto_encabe 
                    SET mon_disponible = $f_disponible, 
                        mon_compromiso_provisional = $f_provisional 
                    WHERE ano_presupuesto = $yearPresupuesto 
                    AND cod_centro = '$centroCosto' 
                    AND cod_subpartida = '$codPartida' 
                    AND cod_meta = '$codMeta'";

            $resultA = $this->execute($sql);

            if ($resultA) {

                $sql = "DELETE FROM PRE_EJECUCION_PRESUPUESTO_DETALL
                        WHERE ano_presupuesto = $yearPresupuesto 
                            AND cod_centro = '$centroCosto' 
                            AND cod_subpartida = '$codPartida' 
                            AND cod_meta = '$codMeta'
                            AND tip_documento = 1
                            AND num_documento = $numSolicitud";

                $resultB = $this->execute($sql);

                if (!$resultB) {
                    $query = "ROLLBACK;";
                    $this->execute($query);

                    $finalStatus = false;
                    $errorMessage = "";
                    $status_process = 0;
                }


            } else {
                $query = "ROLLBACK;";
                $this->execute($query);

                $finalStatus = false;
                $errorMessage = "";
                $status_process = 0;
            }

            
        }

        if($finalStatus) {
            $sql = "UPDATE pro_solicitud_materiales 
                        SET ind_estado = $indEstado, 
                        $fields 
                    WHERE COD_SOLICITUD = $numSolicitud";
            $resultC = $this->execute($sql);   

            if (!$resultC) {
                $status_process = 0;
            }    

            if ($resultC) {
                echo json_encode(array('response'=>1));    
            } else {
                echo json_encode(array('response'=>0));
            }            
        }
        
    }

    // *    $arr - associative multi keys data array
    // *    $group_by_fields - array of fields to group by
    // *    $sum_by_fields - array of fields to calculate sum in group

    private function array_group_by($arr, $group_by_fields = false, $sum_by_fields = false) {
        if ( empty($group_by_fields) ) return; // * nothing to group

        $fld_count = 'grp:count'; // * field for count of grouped records in each record group

        // * format sum by
        if (!empty($sum_by_fields) && !is_array($sum_by_fields)) {
            $sum_by_fields = array($sum_by_fields);
        }

        // * protected  from collecting
        $fields_collected = array();

        // * do
        $out = array();
        foreach($arr as $value) {
            $newval = array();
            $key = '';
            foreach ($group_by_fields as $field) {
                $key .= $value[$field].'_';
                $newval[$field] = $value[$field];
                unset($value[$field]);
            }
            // * format key
            $key = substr($key,0,-1);

            // * count
            if (isset($out[$key])) { // * record already exists
                $out[$key][$fld_count]++;
            } else {
                $out[$key] = $newval;
                $out[$key][$fld_count]=1;
            }
            $newval = $out[$key];

            // * sum by
            if (!empty($sum_by_fields)) {
                foreach ($sum_by_fields as $sum_field) {
                    if (!isset($newval[$sum_field])) $newval[$sum_field] = 0;
                    $newval[$sum_field] += $value[$sum_field];
                    unset($value[$sum_field]);
                }
            }

            // * collect differencies
            if (!empty($value))
                foreach ($value as $field=>$v) if (!is_null($v)) {
                    if (!is_array($v)) {
                        $newval[$field][$v] = $v;
                    } else $newval[$field][join('_', $v)] = $v; // * array values 
                }

            $out[$key] = $newval;
        }
        return array_values($out);
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

    private function getCodigoDetalle() {

        $sql = "SELECT max(cod_detalle) as codDetalle 
                FROM PRE_EJECUCION_PRESUPUESTO_DETALL";
        $result = $this->execute($sql);
        $codigo = $this->getArray($result);

        return $codigo[0]['codDetalle'];
    }

    private function getCodigoDenegado() {

        $sql = "SELECT max(cod_denegado) as codDenegado 
                FROM PRO_SOLICITUDES_DENEGADAS  ";
        $result = $this->execute($sql);
        $codigo = $this->getArray($result);

        return $codigo[0]['codDenegado'];
    }

    private function getCentroCosto($numSolicitud) {

        $sql = "SELECT 
                    psm.cod_centro_costo 
                FROM 
                    PRO_SOLICITUD_MATERIALES as psm 
                WHERE 
                    psm.cod_solicitud = $numSolicitud";

        $result = $this->execute($sql);
        $solicitudCentroCosto = $this->getArray($result);

        return trim($solicitudCentroCosto[0]['cod_centro_costo']);
    }


    private function getDetalleSolicitud($numSolicitud) {

        $sql = "SELECT 
                    COD_META, RTRIM(LTRIM(COD_CUENTA_PRESUPUESTO_SUG)) as COD_PARTIDA, MON_TOTAL 
                FROM 
                    PRO_DETALLE_SOLICITUD_MATERIALES as pdsm 
                WHERE 
                    pdsm.cod_solicitud = $numSolicitud";

        $result = $this->execute($sql);
        $detalleSolicitud = $this->getArray($result);

        return $detalleSolicitud;
    }

    private function getConfiguradores() {

        $sql = "SELECT VAL_DATO 
                FROM SIF_CONFIGURADORES
                WHERE COD_CONFIGURADOR in (13)";

        $result = $this->execute($sql); 
        $configuradores = $this->getArray($result);

        return $configuradores;
    }

    private function getVersion($yearPresupuesto) {

        $sql = "SELECT max(cod_version) as version 
                FROM PRE_PRESUPUESTO_ENCABEZADO
                WHERE ano_presupuesto = $yearPresupuesto";
        $result = $this->execute($sql);
        $version = $this->getArray($result);

        return $version[0]['version'];
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