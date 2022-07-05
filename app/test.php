<?php 

$json = file_get_contents('file.json');
$data = json_decode($json, TRUE);

echo "<pre>";
print_r($data['API']['key']);
echo "</pre>";

/*
$jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($json, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);

print_r($jsonIterator);
*/
/*
foreach ($jsonIterator as $key => $val) {
    if(is_array($val)) {
        echo "$key:<br />";
    } else {
        echo "$key => $val<br />";
    }
}
*/
?>