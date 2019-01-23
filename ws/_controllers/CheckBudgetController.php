<?php 

require ('BaseController.php');
class CheckBudgetController extends BaseController
{

    function __construct($c, $f) {
        parent::__construct($c, $f);
    }

    public function goCheckBudget() {        
        $params = $this->getParameters();

        //Estado de la Consulta
        $state_execution = "0";
        $desc_execution  = "Ejecución completada satisfactoriamente";

        // Campos de la consulta
        $fields_request = array("cedJuridicaInst"  => true,
                                "consCompromiso"   => true,
                                "codCuenta"        => true,
                                "numPartida"       => false,
                                "numAgrupacion"    => false,
                                "numSolicitud"     => true,
                                "numSolicitudPago" => false,
                                "numOrdenPedido"   => false,
                                "montoSolicitud"   => true,
                                "tipoMoneda"       => true,
                                "codEjecucion"     => true,
                                "fecInterface"     => false,
                                "Info_gen1"        => false,
                                "Info_gen2"        => false,
                                "Info_gen3"        => false);
        $params_status = true;
        $list_of_fields_required = array();
        foreach ($fields_request as $key => $field) {
            if (!array_key_exists($key, $params) && $field == true) {
                $params_status = false;
                array_push($list_of_fields_required, $key);
            }
        }

        if (!$params_status) {

            $state_execution = "3";

            $str_fields_required = "";
            foreach ($list_of_fields_required as $f) {
                $str_fields_required .= $f.", ";
            }
            $str_fields_required = substr($str_fields_required, 0, -2);

            $desc_execution  = "Error de Ejecución | Campos requeridos: $str_fields_required";
        }


        //cedJuridicaInst  //VARCHAR2    12  * Cédula Jurídica de la institución.  Campo con 10 números consecutivos, sin guiones ni espacios
        //consCompromiso   //VARCHAR2    30  * Consecutivo Presupuestario    Máximo de 30 caracteres
        //codCuenta        //VARCHAR2    50  * Centro de Costos / Cuenta Presupuestaria  Máximo de 50 caracteres Código de la unidad institucional a la cual pertenece el presupuesto designado. También en este campo se especifica el código de estructura presupuestaria
        //numPartida       //VARCHAR2    3   Número de partida   Máximo 3 caracteres Ej. 999 Este campo sera utilizado en los momentos 02 y 03 (del campo cod_proc) para que la institucion almecene la verificación
        //numAgrupacion    //NUMBER      3,0 Número de agrupación        Este campo sera utilizado en los momentos 04,05,07,11 (del campo cod_proc) para que la institucion almecene la agrupacion de lineas adjudicadas
        //numSolicitud     //VARCHAR2    16  * Numero de solicitud de contratación   "Conformado por un total de 16 dígitos de la siguiente manera: 
        //numSolicitudPago //VARCHAR2    16  Numero de solicitud de pago     Este campo sera utilizado en el momento 04 (del campo cod_proc) para que la institucion almecene el numero de solicitud de pago
        //numOrdenPedido   //VARCHAR2    16  Numero de orden de pedido       Este campo sera utilizado en el momento 11 (del campo cod_proc) para que la institucion almacene el # de orden de pedido
        //montoSolicitud   //NUMBER  21.6    * Monto de presupuesto requerido        Monto sumado del SubTotal de las lineas con un mismo consecutivo presupuestario y cuenta presupuestaria.
        //tipoMoneda       //VARCHAR2    3   * Moneda de monto de presupuesto        Moneda del monto sumado de las lineas con un mismo consecutivo presupuestario y cuenta presupuestaria
        //codEjecucion     //VARCHAR2    2   *Ö Momento de Ejecución de la Interfaz  po
        //fecInterface     //DATE        Fecha y hora de ejecucion       Este campo se utiliza para asociar un grupo de lineas a verificar a un mismo proceso
        //Info_gen1        //VARCHAR2    1000    Monto y moneda Origial      "Monto sumado del SubTotal de las lineas con un mismo consecutivo presupuestario y cuenta presupuestaria en la moneda Original diferente a Colones. Tiene la estructura:
        //Info_gen2        //VARCHAR2    1000    Campo de uso variado
        //Info_gen3        //VARCHAR2    1000    Campo de uso variado

        /*
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        */

        header('Content-type: text/xml');
        echo '<response>';
        echo "<Result>$state_execution</Result>";
        echo "<Result_desc>$desc_execution</Result_desc>";
        if ($state_execution == "0") {
            echo '<Bdgt_use_amt>2,100,000.00</Bdgt_use_amt>';
            echo '<Bdgt_ymd>2019</Bdgt_ymd>';
            echo '<Sub_partida>Nombre Subpartida</Sub_partida>';
        }
        /*
        foreach($posts as $index => $post) {
            if(is_array($post)) {
                foreach($post as $key => $value) {
                    echo '<',$key,'>';
                    if(is_array($value)) {
                        foreach($value as $tag => $val) {
                            echo '<',$tag,'>',htmlentities($val),'</',$tag,'>';
                        }
                    }
                    echo '</',$key,'>';
                }
            }
        }
        */
        echo '</response>';

    }

    /*
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
    */
}

?>