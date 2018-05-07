<?php 

error_reporting(E_ALL);
ini_set("display_errors", 1);

if (isset($_REQUEST["c"])){

	$controller = $_REQUEST["c"];
	$action = $_REQUEST["f"];

	$class = ucfirst($controller)."Controller";	
	require("controllers/$class.php");	
	
	$class = $controller."Controller";
	$object = new $class($controller, $action);
	$object->setParameters($_REQUEST);
	$object->$action();
}
exit;

?>