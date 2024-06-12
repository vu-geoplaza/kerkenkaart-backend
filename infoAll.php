<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 25-11-2020
 */


include('db.php');
include('rowsToGeoJson.php');

$db = new db();
$l=$db->getAll();
$list=[];
$n = 0;
foreach ($l as $row) {
    $list[$n]=$db->getInfo($row['id']);
    $n++;
}

$json = json_encode(createMaximalGeoJSON($list));

header('Content-Type: application/json');
echo $json;
