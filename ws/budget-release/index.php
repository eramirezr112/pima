<?php 

error_reporting(E_ALL);
ini_set("display_errors", 1);

$_REQUEST["c"] = "BudgetRelease";
$_REQUEST["request"] = "getBudgetRelease";

if (isset($_REQUEST["c"])){

	$controller = $_REQUEST["c"];
	$action = $_REQUEST["request"];

	$class = ucfirst($controller)."Controller";	
	require("controllers/$class.php");	
	
	$class = $controller."Controller";
	$object = new $class($controller, $action);
	$object->setParameters($_REQUEST);
	$object->$action();
}
exit;

?>