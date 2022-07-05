<?php 

	$file = "./_encriptacion1/api.php";

	$gestor = fopen($file, "r");

	$texto = base64_encode(gzdeflate(fread($gestor, filesize($file)), 9));

	fclose($gestor);

	$gestor = fopen($file."_encripted.php", "w");

	fwrite($gestor, '<?php eval(gzinflate(base64_decode("'.$texto.'"))); ?>');

	fclose($gestor);

?>