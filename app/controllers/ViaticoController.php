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
        if ($connectionType == "odbc_mssql") {
            $nameSolicitante = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }

        $isAdmin = $this->checkPermision(7);
        $codUsuario  = $_SESSION['cod_usuario'];
        $isJefe      = $_SESSION['rol_web'];

        $table = ['TES_CCHV_ADELANTO_ENC', 'alias'=>'v'];

        $columns = [
            'num_adelanto' =>'solicitud',                        
            'tcc.des_clasificacion' =>'clasificacion',
            'fec_adelanto' =>'fecha',
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

    public function getNumAdelanto(){

        $params = $this->getParameters();
        $numAdelanto = $params["numAdelanto"];

        session_start();
        $connectionType = $_SESSION["CONNECTION_TYPE"];

        $nameFuncionario = 'CONCAT (ssf.des_nombre, SPACE(1),ssf.des_apellido1, SPACE(1), ssf.des_apellido2)';
        if ($connectionType == "odbc_mssql") {
            $nameFuncionario = 'ssf.des_nombre + \' \' + ssf.des_apellido1 + \' \' + ssf.des_apellido2';
        }        

        $sql = "SELECT tcae.num_adelanto,
                       tcae.fec_adelanto,
                       $nameFuncionario as solicitante,
                       tcae.cod_centro_costo,
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
                       tcad.fec_detalle,
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

    public function approveSolicitud() {
        session_start();
        if ($_SESSION['cod_usuario'] != 0) {
            $codUsuario  = $_SESSION['COD_FUNCIONARIO'];
        } else {
            $codUsuario  = 0;
        }

        $params = $this->getParameters();
        $id        = intval($params["codSolicitud"]);
        $codCentro = $params["codCentro"];
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
                       mon_disponible as disponible 
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