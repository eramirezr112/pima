<?php 

require ('config/db.php');
class BaseController extends DBConfig
{
    
    var $controllerName;
    var $functionName;
    var $parameters = array();

    function __construct($c, $f) {
        parent::__construct();
        $this->controllerName = $c;
        $this->functionName = $f;
    }

    public function setParameters($request) {
        $parameter = array();
        foreach ($request as $key => $value) {
            if ($value != $this->controllerName && $value != $this->functionName) {
                $parameter[$key] = $value;              
            }
        }
        array_push($this->parameters, $parameter);
    }

    public function getParameters() {
        return $this->parameters[0];
    }

    public function transformArrayToString($array){
        $stringElements = "";
        foreach ($array as $element) {
            $stringElements .= "$element, ";
        }

        $stringElements = substr_replace($stringElements, "", -2);
        
        return $stringElements;
    }

    public function toUtf8($data) {
        $newData = array();
        foreach ($data as $line) {
            $line = array_map("utf8_encode", $line); 
            array_push($newData, $line);
        }
        return $newData;
    }   
}

?>