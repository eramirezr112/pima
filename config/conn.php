<?php

$serverName = "72.55.156.230"; 
$uid = "usuariomovil";   
$pwd = "android1793";  
$databaseName = "sace"; 

$connectionInfo = array( "UID"=>$uid,                            
                         "PWD"=>$pwd,                            
                         "Database"=>$databaseName); 

/* Connect using SQL Server Authentication. */  
$conn = sqlsrv_connect( $serverName, $connectionInfo);  

$tsql = "SELECT * FROM frm_programas";  
$stmt = sqlsrv_query( $conn, $tsql );
if( $stmt === false) {
    die( print_r( sqlsrv_errors(), true) );
}

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
	echo "<pre>";
      print_r($row);
    echo "</pre>";
}

sqlsrv_free_stmt( $stmt);  
sqlsrv_close( $conn);  
?>