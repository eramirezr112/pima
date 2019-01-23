<?php 

error_reporting(E_ALL);
ini_set("display_errors", 1);

$_REQUEST["c"] = "checkBudget";
$_REQUEST["request"] = "goCheckBudget";

if (isset($_REQUEST["c"])){

	$controller = $_REQUEST["c"];
	$action = $_REQUEST["request"];

	$class = ucfirst($controller)."Controller";	
	require("../_controllers/$class.php");	
	
	$class = $controller."Controller";
	$object = new $class($controller, $action);
	$object->setParameters($_REQUEST);
	$object->$action();
}
exit;

?>