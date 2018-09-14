<?php 
    require ('../app/config/db.php');
    $user     = trim($_POST['user']);
    $pass     = trim($_POST['pass']);
    $realPass = getLoginPassword($pass);
    $db = new DBConfig();
    $jData = getJoinData($user);
    $join_table         = $jData['join_table'];
    $fields_funcionario = $jData['fields_funcionario'];
    $join_condition     = $jData['join_condition'];
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
    $stmt = $db->executeSecure($query, $values);    
    $data = $db->getArray($stmt);

    if (is_array($data) && sizeof($data) > 0) {
        session_start();

        //Set type of connection
        $_SESSION["CONNECTION_TYPE"] = $db->conn->databaseType;

        $user = $data[0];
        $is_admin_login = false;
        if (sizeof($user) <= 7){
            $is_admin_login = true;
        }
            
        if ($user['rol_web'] != null) {
            $query = "SELECT *  
                      FROM web_permisos as wp 
                      WHERE wp.rol_usuario = ?";
            
            $values = array($user['rol_web']);
            $stmt = $db->executeSecure($query, $values); 
            $permits = $db->getArray($stmt);
            $_SESSION['cod_usuario']    = $user["cod_usuario"];
            $_SESSION['des_usuario']    = $user["des_usuario"];
            $_SESSION['des_login']      = $user["des_login"];   
            $_SESSION['ind_estado']     = $user["ind_estado"];
            $_SESSION['ind_bloqueo']    = $user["ind_bloqueo"];
            $_SESSION['rol_web']        = $user['rol_web'];
            $_SESSION['modulos_acceso'] = $permits[0]['acceso_modulo'];
            $_SESSION['opt_sin_acceso'] = $permits[0]['opciones_sin_acceso'];
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
                $type_empleado = getTypeEmpleado($db, $user["COD_FUNCIONARIO"]);

                $funcionarios_in_charge = "";
                // GERENTE
                if ($type_empleado['isGerente']) {
                    // Se obtiene la lista de funcionarios a cargo del Gerente
                    $query = "SELECT cc.cod_encargado FROM rh_centros_costo as cc 
                              WHERE cc.cod_centro_padre is NULL AND cc.cod_centro != ?";
                    $values = array('04');
                    $stmt = $db->executeSecure($query, $values); 
                    $listFuncionarios = $db->getArray($stmt);
                    foreach ($listFuncionarios as $f) {
                        $funcionarios_in_charge .= $f['cod_encargado']. ", ";
                    }
                    // Se obtiene los funcionarios que pertenecen al centro de costo de gerencia (cod_centro = 07)
                    // (U)
                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                              WHERE f.cod_centro = ? AND f.ind_estado != ?";
                    $values = array(trim($user["COD_CENTRO"]), 0);
                    $stmt = $db->executeSecure($query, $values); 
                    $listFuncionariosLocales = $db->getArray($stmt);
                    foreach ($listFuncionariosLocales as $f) {
                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                    }
                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
                } else {
                    // AUDITOR
                    if ($type_empleado['isAuditor']) {
                        
                        // Se obtiene los funcionarios que pertenecen al centro de costo del auditor
                        $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                  WHERE f.cod_centro = ? AND f.ind_estado != ?";
                        $values = array(trim($user["COD_CENTRO"]), 0);
                        $stmt = $db->executeSecure($query, $values); 
                        $listFuncionariosLocales = $db->getArray($stmt);
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
                        $stmt = $db->executeSecure($query, $values); 
                        $isCodEncargado = $db->getArray($stmt);
                        //DIRECTOR
                        if (sizeof($isCodEncargado) > 0) {
                            /*
                            echo "ES DIRECTOR <br />";
                            echo "============================== <br />";
                            */
                            $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                      WHERE f.cod_centro like '".trim($isCodEncargado[0]['cod_centro'])."%' 
                                      AND f.ind_estado != ? 
                                      AND f.COD_FUNCIONARIO != ?";
                            
                            $values = array(0, $user["COD_FUNCIONARIO"]);
                            $stmt = $db->executeSecure($query, $values);  
                            $listFuncionariosLocales = $db->getArray($stmt);
                            foreach ($listFuncionariosLocales as $f) {
                                $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                            }
                            $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);

                        // LIDERES DE PROCESO Y SUB-PROCESO
                        } else {

                            // Se verifica si el usuario es lider con subprocesos
                            $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                      WHERE cc.cod_centro_padre is not NULL 
                                      AND cc.cod_centro in (SELECT bb.cod_centro_padre FROM rh_centros_costo as bb)  
                                      AND cc.cod_encargado = ?";
                            $values = array($user["COD_FUNCIONARIO"]);
                            $stmt = $db->executeSecure($query, $values);  
             
                            $isLiderSubproceso = $db->getArray($stmt);
      
                            if (sizeof($isLiderSubproceso) > 0) {

                                $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                          WHERE f.cod_centro like '".trim($isLiderSubproceso[0]["cod_centro"])."%' 
                                          AND f.ind_estado != ? 
                                          AND f.COD_FUNCIONARIO != ?";
       
                                $values = array(0, $user["COD_FUNCIONARIO"]);
                                $stmt = $db->executeSecure($query, $values);
                                $listFuncionariosLocales = $db->getArray($stmt);                                      
                                foreach ($listFuncionariosLocales as $f) {
                                    $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                }
                                $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
                            } else {            

                                // COMENTARIO
                                
                                //echo "ES LIDER DE PROCESO <br />";
                                //echo "============================== <br />";
                                
                                $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                          WHERE f.cod_centro like '".trim($user["COD_CENTRO"])."%' 
                                          AND f.ind_estado != ? 
                                          AND f.COD_FUNCIONARIO != ?";
                                
                                // COMENTARIO
                                //echo $query;
                                   
                                $values = array(0, $user["COD_FUNCIONARIO"]);
                                $stmt = $db->executeSecure($query, $values);
                                $listFuncionariosLocales = $db->getArray($stmt);
                                foreach ($listFuncionariosLocales as $f) {
                                    $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                }
                                $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
        
                            }

                            /*
                            $query = "SELECT DISTINCT(ad.cod_centro) FROM rh_autorizados_direccion as ad 
                                      WHERE ad.cod_funcionario = ?";
                            $values = array($user["COD_FUNCIONARIO"]);
                            $stmt = $db->executeSecure($query, $values);  
                            $isCodAutorizado = $db->getArray($stmt);
                            //AUTORIZADO
                            if (sizeof($isCodAutorizado) > 0) {
                            */
                            if (true) {
                                /*  
                                // COMENTARIO
                                
                                echo "ES AUTORIZADO <br />";
                                echo "============================== <br />";
                                
                                // Se consulta si el autorizado es un Lider de proceso
                                $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                          WHERE cc.cod_encargado = ?";
                                $values = array($user["COD_FUNCIONARIO"]);
                                $stmt = $db->executeSecure($query, $values);  
                                // COMENTARIO
                                
                                //echo "<br />";
                                //echo $query;
                                //echo "<br />";
                                //echo "<br />";
                                
                                $isEncargado = $db->getArray($stmt);
                                $funcionariosLiderProceso = array();
                                if (sizeof($isEncargado) > 0) {
                                    // COMENTARIO
                                    
                                    //echo "Y LIDER DE PROCESO <br />";
                                    //echo "============================== <br />";
                                    //echo "<pre>";
                                    //print_r($isEncargado);
                                    //echo "</pre>";
                                    
                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($user["COD_CENTRO"])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";                                    
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $db->executeSecure($query, $values);  
                                    $funcionariosLiderProceso = $db->getArray($stmt);
                                    // COMENTARIO
                                    
                                    //echo "<pre>";
                                    //print_r($funcionariosLiderProceso);
                                    //echo "</pre>";
                                    
                                }
                                $funcionarios_del_autorizado = array();
                                foreach ($isCodAutorizado as $cc) {
                                    // COMENTARIO
                                    
                                    //echo $cc['cod_centro']."<br >";
                                    //echo "================================= <br />";
                                    
                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($cc['cod_centro'])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $db->executeSecure($query, $values);
                                    $list = $db->getArray($stmt);
                                    foreach ($list as $lC) {
                                        
                                        array_push($funcionarios_del_autorizado, $lC['COD_FUNCIONARIO']);
                                    }
                                    // COMENTARIO
                                    
                                    //echo "<pre>";
                                    //print_r($funcionarios_del_autorizado);
                                    //echo "</pre>";
                                    
                                    foreach ($funcionarios_del_autorizado as $f) {
                                        $funcionarios_in_charge .= $f. ", ";
                                    }  
                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0);                                  
                                }
                                
                                // Se adjunta la lista de funcionarios como Lider de Proceso
                                if (sizeof($funcionariosLiderProceso) > 0) {
                                    foreach ($funcionariosLiderProceso as $f) {
                                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                    }
                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
                                    echo $funcionarios_in_charge;
                                }
                                */
                            // LIDER DE PROCESO (MOVER esto al else DIRECTOR)
                            } else {

                                /*
                                // Se verifica si el usuario es lider con subprocesos
                                $query = "SELECT cc.cod_centro FROM rh_centros_costo as cc 
                                          WHERE cc.cod_centro_padre is not NULL 
                                          AND cc.cod_centro in (SELECT bb.cod_centro_padre FROM rh_centros_costo as bb)  
                                          AND cc.cod_encargado = ?";
                                $values = array($user["COD_FUNCIONARIO"]);
                                $stmt = $db->executeSecure($query, $values);  
                 
                                $isLiderSubproceso = $db->getArray($stmt);
          
                                if (sizeof($isLiderSubproceso) > 0) {

                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($isLiderSubproceso[0]["cod_centro"])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";
           
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $db->executeSecure($query, $values);
                                    $listFuncionariosLocales = $db->getArray($stmt);                                      
                                    foreach ($listFuncionariosLocales as $f) {
                                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                    }
                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
                                } else {            

                                    // COMENTARIO
                                    
                                    //echo "ES LIDER DE PROCESO <br />";
                                    //echo "============================== <br />";
                                    
                                    $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                              WHERE f.cod_centro like '".trim($user["COD_CENTRO"])."%' 
                                              AND f.ind_estado != ? 
                                              AND f.COD_FUNCIONARIO != ?";
                                    
                                    // COMENTARIO
                                    //echo $query;
                                       
                                    $values = array(0, $user["COD_FUNCIONARIO"]);
                                    $stmt = $db->executeSecure($query, $values);
                                    $listFuncionariosLocales = $db->getArray($stmt);
                                    foreach ($listFuncionariosLocales as $f) {
                                        $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                                    }
                                    $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
            
                                }
                                */
                            }
                        }
                        /*
                        $query = "SELECT f.COD_FUNCIONARIO FROM rh_funcionarios as f 
                                  WHERE f.cod_centro like '".$user["COD_CENTRO"]."%' AND f.ind_estado != 0 AND f.COD_FUNCIONARIO != ".$user["COD_FUNCIONARIO"];
                        $stmt = $db->executeSecure($query); 
                        $listFuncionariosLocales = $db->getArray($stmt);
                        */
                        /*
                        foreach ($listFuncionariosLocales as $f) {
                            $funcionarios_in_charge .= $f['COD_FUNCIONARIO']. ", ";
                        }
                        $funcionarios_in_charge = substr($funcionarios_in_charge, 0, -2);
                        */
                    }
                }


                /**
                 * Proceso independiente para conocer si el usuario es Autorizado
                 */
                $funcionarios_in_charge_2 = "";
                $query = "SELECT DISTINCT(ad.cod_centro) FROM rh_autorizados_direccion as ad 
                          WHERE ad.cod_funcionario = ?";
                $values = array($user["COD_FUNCIONARIO"]);
                $stmt = $db->executeSecure($query, $values);  
                $isCodAutorizado = $db->getArray($stmt);
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
                        $stmt = $db->executeSecure($query, $values);
                        $list = $db->getArray($stmt);

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


                $_SESSION['FUNCIONARIOS_A_CARGO'] = $funcionarios_in_charge;
                $_SESSION['COD_EMPLEADO'] = $type_empleado['cod_empleado'];
                // Obtencion de los Centros Costo
                // ======================================
                $query = "SELECT cc.cod_centro 
                          FROM rh_centros_costo as cc
                          WHERE cc.cod_encargado = ?";
                
                $values = array($user["COD_FUNCIONARIO"]);
                $stmt = $db->executeSecure($query, $values); 
                $centros_costo = $db->getArray($stmt);
                $allCentrosCosto = [];
                foreach ($centros_costo as $centro) {
                    array_push($allCentrosCosto, $centro['cod_centro']);
                }
                $query = "SELECT ad.cod_centro 
                          FROM rh_autorizados_direccion as ad
                          WHERE ad.cod_funcionario = ?";
                
                $values = array($user["COD_FUNCIONARIO"]);
                $stmt = $db->executeSecure($query, $values); 
                $centros_costo_autorizados_direccion = $db->getArray($stmt);
                foreach ($centros_costo_autorizados_direccion as $centro) {
                    array_push($allCentrosCosto, $centro['cod_centro']);
                }
                // Se colocan los centros de costo en session.
                $_SESSION['CENTROS_COSTO'] = $allCentrosCosto;
            }
            $key = getUniqueLoginAccess();
            $_SESSION['login_token']  = $key;
            
            // COMENTARIO
          
            header("Location: ../app");
        } else {
            session_destroy();
            echo "<script>";
            echo "alert('Este usuario No tiene permisos asignados. Favor comuniquese con el Administrador');";
            echo "window.location = '../';";
            echo "</script>";
            exit;
        }
    } else {
        header("Location: ../");
    }

    function getTypeEmpleado($conexion, $codFuncionario) {
        $query = "SELECT e.cod_empleado 
                FROM sif_empleados_pima as e 
                WHERE e.cod_funcionario = ?";               
        $filter_values = array($codFuncionario);
        $stmt = $conexion->executeSecure($query, $filter_values); 
        $cod_empleado = $conexion->getArray($stmt);
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
    /**
     * Get the truly password access for the user
     * @param $pass password provided by th web form
     */
    function getLoginPassword($pass) {
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
    /**
     *
     */
    function getUniqueLoginAccess () {
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
        return $key;
    }
    function getJoinData($user){
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
?>