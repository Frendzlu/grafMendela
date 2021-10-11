<?php
header('Content-Type: application/json');
require_once('./class.php');
$jsonData = json_encode($mainInstance->image->mapData);
echo $jsonData."\n";
