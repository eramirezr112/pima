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

        /*
        SELECT num_adelanto,  
             cod_clasificacion,  
             fec_adelanto,  
             cod_solicitante,  
             mon_adelanto,  
             cod_estado,  
             fec_autorizacion,  
             cod_autoriza,  
             cod_centro_costo,  
             cod_presupuesto,  
             COD_META 
        FROM TES_CCHV_ADELANTO_ENC 
        WHERE num_adelanto = :an_adelanto 
            AND fec_adelanto between :ad_inicial and :ad_final 
            AND cod_solicitante = :an_solicitante or :an_solicitante = -1 
            AND cod_estado = 1 
            AND cod_centro_costo in ( :as_centro )  
            AND cod_solicitante not in (SELECT COD_ENCARGADO 
                                        FROM RH_CENTROS_COSTO 
                                        WHERE 
                                            COD_CENTRO in (:as_centro)
                                        )
        */

        $table = ['TES_CCHV_ADELANTO_ENC', 'alias'=>'v'];

        $columns = [
            'num_adelanto' =>'numero adelanto',            
            "$nameSolicitante" => 'solicitante',
            'fec_adelanto' =>'fecha',
            'cod_clasificacion' =>'clasificacion',
            'mon_adelanto' =>'monto',
            'cod_estado' =>'estado'
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

        $result = $this->customExecute($table, $columns, $conditions, $relations);
        $query = $this->getQueryString();
        $solicitudes = $this->getArray($result);
        //$solicitudes = array();

        echo json_encode(array('columns'=>$columns,'adelantoViaticos'=>$solicitudes, 'query' => $query));
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