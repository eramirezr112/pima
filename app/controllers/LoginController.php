<?php 

require ('BaseController.php');
class LoginController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function validate() {
        $params = $this->getParameters();

        $user = trim($params['nickname']);
        $pass = trim($params['password']);

        $realPass = $this->getLoginPassword($pass);
        
        $jData = $this->getJoinData($user);
        $join_table         = $jData['join_table'];
        $fields_funcionario = $jData['fields_funcionario'];
        $join_condition     = $jData['join_condition'];

        // Se valida que el usuario ingresado exista
        $query = "SELECT COUNT(u.cod_usuario) as existencia FROM seg_usuarios as u WHERE u.des_login = ?";
        $values = array($user);
        $stmt = $this->executeSecure($query, $values);
        $userExist = $this->getArray($stmt);
     
        $response = null;
        if ($userExist[0]['existencia'] == 0) {            
            $response = -2;
        }

        $query = "SELECT u.cod_usuario, 
                    u.des_usuario, 
                    u.des_login, 
                    u.ind_estado, 
                    u.cod_perfil, 
                    u.ind_bloqueo,
                    u.rol_web 
                    $fields_funcionario
                  FROM seg_usuarios as u $join_table
                  WHERE des_login = ? 
                    AND des_password = ? 
                    $join_condition";
        $values = array($user, $realPass);

        $stmt = $this->executeSecure($query, $values);    
        $data = $this->getArray($stmt);

        // Usuario Encontrado
        if (is_array($data) && sizeof($data) > 0) {

            session_start();

            //Set type of connection
            $_SESSION["CONNECTION_TYPE"] = $this->conn->databaseType;

            // Se valida si el usuario es administrador
            $user = $data[0];
            $is_admin_login = false;
            if (sizeof($user) <= 7){
                $is_admin_login = true;
            }

            // Usuario con permisos de acceso Web
            if ($user['rol_web'] != null) {

                $response = 1;

                $query = "SELECT *  
                          FROM web_permisos as wp 
                          WHERE wp.rol_usuario = ?";
                
                $values = array($user['rol_web']);
                $stmt = $this->executeSecure($query, $values); 
                $permits = $this->getArray($stmt);
                $_SESSION['cod_usuario']    = $user["cod_usuario"];
                $_SESSION['des_usuario']    = $user["des_usuario"];
                $_SESSION['des_login']      = $user["des_login"];
                $_SESSION['ind_estado']     = $user["ind_estado"];
                $_SESSION['ind_bloqueo']    = $user["ind_bloqueo"];
                $_SESSION['rol_web']        = $user['rol_web'];
                $_SESSION['modulos_acceso'] = $permits[0]['acceso_modulo'];
                $_SESSION['opt_sin_acceso'] = $permits[0]['opciones_sin_acceso'];

                if ($is_admin_login == true) {
                    $_SESSION['TIPO_FUNCIONARIO'] = "Super Admin";
                }

                if ($is_admin_login == false) {

                    $funcionario = array();
                    $funcionario['COD_FUNCIONARIO']         = $user["COD_FUNCIONARIO"];
                    $funcionario['DES_CEDULA']              = $user["DES_CEDULA"];
                    $funcionario['DES_APELLIDO1']           = $user["DES_APELLIDO1"];
                    $funcionario['DES_APELLIDO2']           = $user["DES_APELLIDO2"];
                    $funcionario['DES_NOMBRE']              = $user["DES_NOMBRE"];
                    $funcionario['COD_CENTRO']              = $user["COD_CENTRO"];
                    $funcionario['IND_TIPO_IDENTIFICACION'] = $user["IND_TIPO_IDENTIFICACION"];
                    $funcionario['IND_ESTADO_FUNC']         = $user["IND_ESTADO"];
                    foreach ($funcionario as $key => $value) {
                        $_SESSION[$key] = $value;
                    }

                    // Verifica si el usuario que ingresa es GERENTE
                    $type_empleado = $this->getTypeEmpleado($user["COD_FUNCIONARIO"]);

                    $funcionarios_in_charge = "";
                    // GERENTE
                    if ($type_empleado['isGerente']) {

                        $_SESSION['TIPO_FUNCIONARIO'] = 'GERENTE';

                        // Se obtiene la lista de funcionarios a cargo del Gerente
                        $query = "SELECT cc.cod_encargado FROM rh_centros_costo as cc 
                                  WHERE cc.cod_centro_padre is NULL AND cc.cod_centro != ?";
                        $values = array('04');
                        $stmt = $this->executeSecure($query, $values); 
                        $listFuncionarios = $this->getArray($stmt);
                        foreach ($listFuncionarios as $f) {
                            $funcionarios_in_charge .= $f['cod_encargado']. ", ";
                        }

                        // Se obtiene los funcionarios que pertenecen al centro de costo de gerencia (cod_centro = 07)
                        $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                  WHERE f.cod_centro = ? AND f.ind_estado != ?";
                        $values = array(trim($user["COD_CENTRO"]), 0);
                        $stmt = $this->executeSecure($query, $values); 
                        $listFuncionariosLocales = $this->getArray($stmt);
                        foreach ($listFuncionariosLocales as $f) {
                            $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                        }
                        $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                    } else {

                        // AUDITOR
                        if ($type_empleado['isAuditor']) {

                            $_SESSION['TIPO_FUNCIONARIO'] = 'AUDITOR';

                            // Se obtiene los funcionarios que pertenecen al centro de costo del auditor
                            $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                      WHERE f.cod_centro = ? AND f.ind_estado != ?";
                            $values = array(trim($user["COD_CENTRO"]), 0);
                            $stmt = $this->executeSecure($query, $values); 
                            $listFuncionariosLocales = $this->getArray($stmt);
                            foreach ($listFuncionariosLocales as $f) {
                                $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                            }
                            $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                        } else {

                            /**
                             * Usuarios: Directores, Lideres de Proceso 
                             */
                            $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                      WHERE cc.cod_centro_padre is NULL AND cc.cod_encargado = ?";
                            $values = array($user["COD_FUNCIONARIO"]);
                            $stmt = $this->executeSecure($query, $values); 
                            $isCodEncargado = $this->getArray($stmt);

                            //DIRECTOR
                            if (sizeof($isCodEncargado) > 0) {

                                $_SESSION['TIPO_FUNCIONARIO'] = $this->getTypeDirector($isCodEncargado[0]['cod_centro']);

                                $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                          WHERE f.cod_centro like '".trim($isCodEncargado[0]['cod_centro'])."%' 
                                          AND f.ind_estado != ? 
                                          AND f.COD_FUNCIONARIO != ?";
                                
                                $values = array(0, $user["COD_FUNCIONARIO"]);
                                $stmt = $this->executeSecure($query, $values);  
                                $listFuncionariosLocales = $this->getArray($stmt);
                                foreach ($listFuncionariosLocales as $f) {
                                    $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                }
                                $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                            // LIDERES DE PROCESO Y SUB-PROCESO
                            } else {

                                $_SESSION['TIPO_FUNCIONARIO'] = 'JEFE';

                                // Se verifica si el usuario es lider con subprocesos
                                $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                          WHERE cc.cod_centro_padre is not NULL 
                                          AND cc.cod_centro in (SELECT bb.cod_centro_padre FROM rh_centros_costo as bb)  
                                          AND cc.cod_encargado = ?";
                                $values = array($user["COD_FUNCIONARIO"]);
                                $stmt = $this->executeSecure($query, $values);  
                 
                                $isLiderSubproceso = $this->getArray($stmt);

                                if (sizeof($isLiderSubproceso) > 0) {

                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($isLiderSubproceso[0]["cod_centro"])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";
           
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $this->executeSecure($query, $values);
                                    $listFuncionariosLocales = $this->getArray($stmt);
                                    foreach ($listFuncionariosLocales as $f) {
                                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                    }
                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                                } else {

                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($user["COD_CENTRO"])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";
                                    
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $this->executeSecure($query, $values);
                                    $listFuncionariosLocales = $this->getArray($stmt);
                                    foreach ($listFuncionariosLocales as $f) {
                                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                    }

                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                                }

                            }

                        }

                    }


                    /**
                     * Proceso independiente para conocer si el usuario es Autorizado
                     */
                    $funcionarios_in_charge_2 = "";
                    $query = "SELECT DISTINCT(ad.cod_centro) FROM rh_autorizados_direccion as ad 
                              WHERE ad.cod_funcionario = ?";
                    $values = array($user["COD_FUNCIONARIO"]);
                    $stmt = $this->executeSecure($query, $values);
                    $isCodAutorizado = $this->getArray($stmt);

                    //AUTORIZADO
                    if (sizeof($isCodAutorizado) > 0) {

                        $funcionarios_del_autorizado = array();
                        foreach ($isCodAutorizado as $cc) {

                            $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                      WHERE f.cod_centro like '".trim($cc['cod_centro'])."%' 
                                      AND f.ind_estado != ? 
                                      AND f.COD_FUNCIONARIO != ? 
                                      AND f.COD_FUNCIONARIO not in (SELECT rhcc.cod_encargado FROM rh_centros_costo as rhcc WHERE rhcc.cod_centro = '".trim($cc['cod_centro'])."')";
                            $values = array(0, $user["COD_FUNCIONARIO"]);
                            $stmt = $this->executeSecure($query, $values);
                            $list = $this->getArray($stmt);

                            foreach ($list as $lC) {
                                array_push($funcionarios_del_autorizado, $lC['COD_FUNCIONARIO']);
                            }

                            foreach ($funcionarios_del_autorizado as $f) {
                                $funcionarios_in_charge_2 .= $f. ", ";
                            }
                        }

                    }

                    // SE UNIFICAN LAS LISTAS
                    if ($funcionarios_in_charge_2 != "") {
                        
                        $funcionarios_in_charge_2 = substr($funcionarios_in_charge_2, 0, -2);

                        if ($funcionarios_in_charge != $funcionarios_in_charge_2) {

                            $funcionarios_in_charge .= ", ".$funcionarios_in_charge_2;
                        }
                    }

                    // Se colocan los Funcionarios a Cargo en Session
                    $_SESSION['FUNCIONARIOS_A_CARGO'] = $funcionarios_in_charge;
                    $_SESSION['COD_EMPLEADO']         = $type_empleado['cod_empleado'];

                    // Obtencion de los Centros Costo
                    // ======================================
                    $query = "SELECT cc.cod_centro 
                              FROM rh_centros_costo as cc
                              WHERE cc.cod_encargado = ?";
                    
                    $values = array($user["COD_FUNCIONARIO"]);
                    $stmt = $this->executeSecure($query, $values); 
                    $centros_costo = $this->getArray($stmt);
                    $allCentrosCosto = [];
                    foreach ($centros_costo as $centro) {
                        array_push($allCentrosCosto, trim($centro['cod_centro']));
                    }
                    $query = "SELECT ad.cod_centro 
                              FROM rh_autorizados_direccion as ad
                              WHERE ad.cod_funcionario = ?";
                    
                    $values = array($user["COD_FUNCIONARIO"]);
                    $stmt = $this->executeSecure($query, $values); 
                    $centros_costo_autorizados_direccion = $this->getArray($stmt);
                    foreach ($centros_costo_autorizados_direccion as $centro) {
                        array_push($allCentrosCosto, trim($centro['cod_centro']));
                    }

                    // Se colocan los centros de costo en session.
                    $_SESSION['CENTROS_COSTO'] = $allCentrosCosto;


                }

                $key = $this->getUniqueLoginAccess();
                $_SESSION['login_token']  = $key;                

            // Usuario sin permisos de acceso Web
            } else {
                session_destroy();
                $response = -1;
            }

        // Usuario NO encontrado
        } else {

            if ($response == -2) {
                $response = $response;
            } else {
                $response = 0;
            }
        }


        // Se devuelve el estado del usuario 
        $data_result = array('response'=>$response);

        echo json_encode($data_result);

    }

    /**
     * Get the truly password access for the user
     * @param $pass password provided by th web form
     */
    private function getLoginPassword($pass) {
        $passArray = str_split($pass);
        $passVal = 0;
        $listVals = array();
        for ($i=0; $i < sizeof($passArray); $i++) { 
            $passVal = $passVal + (ord($passArray[$i]) * ($i+1));
            array_push($listVals, $passVal);        
        }
        $expo2 = pow($passVal, 2) * 8;  
        if ($expo2 > 2147483647) {
            $expo2 = 0;
        }
        $expo9 = pow($passVal, 9) * 8;
        if ($expo9 > 2147483647) {
            $expo9 = 0;
        }
        $realPass = $expo2 + $expo9;
        return $realPass;
    }

    private function getUniqueLoginAccess () {
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
        return $key;
    }

    private function getJoinData($user){
        $join_table         = "";
        $fields_funcionario = "";
        $join_condition     = "";
        if ($user != "admin") {
            $join_table = ", rh_funcionarios as f ";
            $fields_funcionario = ", f.COD_FUNCIONARIO, 
                f.DES_CEDULA, 
                f.DES_APELLIDO1, 
                f.DES_APELLIDO2, 
                f.DES_NOMBRE, 
                f.COD_CENTRO, 
                f.IND_TIPO_IDENTIFICACION, 
                f.IND_ESTADO";
            $join_condition = "AND u.cod_funcionario = f.cod_funcionario";
        }
        return array (
            "join_table"         => $join_table,
            "fields_funcionario" => $fields_funcionario,
            "join_condition"     => $join_condition,
        );
    }

    private function getTypeEmpleado($codFuncionario) {
        $query = "SELECT e.cod_empleado 
                FROM sif_empleados_pima as e 
                WHERE e.cod_funcionario = ?";               
        $filter_values = array($codFuncionario);
        $stmt = $this->executeSecure($query, $filter_values); 
        $cod_empleado = $this->getArray($stmt);
        $data = array();
        $data['isGerente'] = false;
        $data['isAuditor'] = false;
        $data['isRegular'] = false;
        if (sizeof($cod_empleado) > 0) {
            $maxPos = sizeof($cod_empleado) - 1;
            if (trim($cod_empleado[$maxPos]['cod_empleado']) == "GGP") {
                $data['isGerente']    = true;
                $data['cod_empleado'] = $cod_empleado[$maxPos]['cod_empleado'];
            } else if (trim($cod_empleado[$maxPos]['cod_empleado']) == "AUD" || trim($cod_empleado[$maxPos]['cod_empleado']) == "AD") {
                $data['isAuditor'] = true;
                $data['cod_empleado'] = $cod_empleado[$maxPos]['cod_empleado'];
            } else {
                $data['isRegular']    = true;
                $data['cod_empleado'] = 'OTHER';
            }
            
        } else {
            $data['isRegular']    = true;
            $data['cod_empleado'] = 'OTHER';
        }
        return $data;
    }

    private function getTypeDirector($codEncargado) {

        $listConfiguradores = array (60, 25);

        $listTypesDirectores = array();

        foreach ($listConfiguradores as $codConfigurador) {

            $query = "SELECT val_dato, cod_configurador 
                      FROM SIF_CONFIGURADORES 
                      WHERE cod_configurador = ?;";
            $filter_values = array($codConfigurador);
            $stmt = $this->executeSecure($query, $filter_values); 
            $dato = $this->getArray($stmt);

            array_push($listTypesDirectores, $dato[0]);

        }

        $typeDirector = "DIRECTOR";
        foreach ($listTypesDirectores as $tDir) {

            // CONFIGURADOR 60
            if ( (trim($tDir['val_dato']) == $codEncargado) && (trim($tDir['cod_configurador']) == $listConfiguradores[0]) ) {
                $typeDirector = "DIR. FINANCIERO";

            // CONFIGURADOR 25                
            } else if ( ($tDir['val_dato'] == $codEncargado) && ($tDir['cod_configurador'] == $listConfiguradores[1]) ) {
                $typeDirector = "JEFE SERVICIOS GENERALES";
            }

        }

        return $typeDirector;
    }    

}

?>