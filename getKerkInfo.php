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
$id = 0;
$pand_id = 0;
if (isset($_GET['id'])) {
    $id = (integer)$_GET['id'];
} elseif (isset($_GET['pand_id'])) {
    $pand_id = filter_input(INPUT_GET, 'pand_id', FILTER_VALIDATE_REGEXP, array(
        "options" => array("regexp" => "/^\d.+$/")
    ));
} else {
    $id = (integer)$_POST['id'];
}

$db = new db();
if ($id !== 0) {
    $l[0] = $db->getInfo($id);
} elseif ($pand_id !== 0) {
    $l[0] = $db->getInfoByPandId($pand_id);
}

$json = json_encode(createMaximalGeoJSON($l));

header('Content-Type: application/json');
echo $json;
