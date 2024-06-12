<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
include('db.php');

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

$list=$_POST['list'];
$filter=$_POST['filter'];
//PV should run new query when a filter is active

$db = new db();
$data = $db->getTypeaheadList($filter,$list);

header('Content-Type: application/json');
echo json_encode($data);
