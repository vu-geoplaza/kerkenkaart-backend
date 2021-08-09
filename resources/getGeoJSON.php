<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
include('db.php');
include('rowsToGeoJson.php');
$db = new db();

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

if (isset($_POST['cluster'])) {
    $cluster=$_POST['cluster'];
    if ($cluster=='provincie'){
        $pl=$db->getProvincies();
        $col='provincie';
    } elseif (($cluster=='gemeente')) {
        $pl=$db->getGemeenten();
        $col='gemeente';
    }
    $c=true;
} else {
    $c=false;
}




if (isset($_POST['filter'])) {
    if ($c) {
        $data = createPlaceGeoJSON($db->getFiltered($_POST['filter']), $col, $pl);
    } else {
        $data = createMinimalGeoJSON($db->getFiltered($_POST['filter']));
    }
} else {
    if ($c){
        $data = createPlaceGeoJSON($db->getAll(), $col, $pl);
    } else {
        $data = createMinimalGeoJSON($db->getAll());
    }
}
header('Content-Type: application/json');
echo json_encode($data);
