<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
include('db.php');
include('rowsToGeoJson.php');
$db = new db();

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

if (isset($_POST['filter'])) {
    $data = createPlaceGeoJSON($db->getFiltered($_POST['filter']));
} else {
    $data = createPlaceGeoJSON($db->getAll(), $col, $pl);
}
header('Content-Type: application/json');
echo json_encode($data);