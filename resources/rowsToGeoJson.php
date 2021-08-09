<?php
$stijlen = ["neogotiek", "modernisme - functionalisme", "expressionisme", "traditionalisme", "neorenaissance", "eclecticisme", "neoromaans", "classicisme", "gotiek", "renaissance", "romaans", "neoclassicisme", "neobarok", "rationalisme", "overig"];
$denominaties = ["Christelijke Gereformeerde Kerk", "Christian Science Church", "Doopsgezinde Sociëteit", "Evangelisch Lutherse Kerk", "Gereformeerde Gemeente (in Nederland)", "Gereformeerde Kerk (vrijgemaakt)", "Gereformeerde Kerken", "Nederlandse Hervormde Kerk", "Nederlandse Protestantenbond", "Oud-Katholieke Kerk", "Protestantse Kerk Nederland", "Remonstrantse Broederschap", "Rooms-katholieke Kerk"];

Function createMinimalGeoJSON($l)
{
    global $stijlen, $denominaties;
    $geo = new stdClass();
    $geo->type = "FeatureCollection";
    $geo->attribution = 'Wesselink, H. (2020). Kerkenkaart [Data set]. Retrieved on ' . date("F j, Y") . ', from https://geoplaza.vu.nl/projects/kerken';
    $geo->features = array();
    $n = 0;
    foreach ($l as $row) {
        $geo->features[$n] = new stdClass();
        $geo->features[$n]->type = "Feature";
        $geo->features[$n]->geometry = new stdClass();
        $geo->features[$n]->properties = new stdClass();
        $geo->features[$n]->geometry->type = "Point";
        $geo->features[$n]->geometry->coordinates[0] = (double)$row['lon'];
        $geo->features[$n]->geometry->coordinates[1] = (double)$row['lat'];

        $geo->features[$n]->properties->id = $row['id'];
        $geo->features[$n]->properties->naam = $row['naam'];
        $denominatie_column = 'denominatie_laatst'; // or 'denominatie' slightly different queries
        if ($row[$denominatie_column] == 'Nederlandse Protestanten Bond') {
            $row[$denominatie_column] = 'Nederlandse Protestantenbond';
        }
        if (!in_array($row[$denominatie_column], $denominaties)) {
            $row[$denominatie_column] = 'Overig';
        }
        $geo->features[$n]->properties->denominatie = $row[$denominatie_column];
        $geo->features[$n]->properties->type = $row['type'];
        if ($row['stijl'] == "classisisme") { //typo
            $row['stijl'] = "classicisme";
        }
        if (in_array($row['stijl'], $stijlen)) {
            $geo->features[$n]->properties->stijl = $row['stijl'];
        } else {
            $geo->features[$n]->properties->stijl = "overig";
        }

        if ($row['huidige_bestemming'] == 'kerk') {
            $hb = 'kerk';
        } else {
            $hb = 'anders';
        }
        $geo->features[$n]->properties->huidige_bestemming = $hb;
        $geo->features[$n]->properties->plaats = $row['plaats'];
        $geo->features[$n]->properties->monument = $row['monument'];
        $geo->features[$n]->properties->ingebruik = $row['ingebruik'];
        $geo->features[$n]->properties->periode = $row['periode'];
        $geo->features[$n]->properties->vorm = $row['type'];
        $n++;
    }
    return $geo;
}

Function arrValues($k, $properties, $r)
{
    $properties->{$k} = [];
    $i = 0;
    foreach ($r as $arrval) {
        foreach ($arrval as $key => $value) {
            if ($key !== 'ID') {
                $properties->{$k}[$i]->{$key} = isset($value) ? $value : '';
            }
        }
        $i++;
    }
    return $properties;
}

Function createMaximalGeoJSON($l)
{
    global $stijlen, $denominaties;
    $geo = new stdClass();
    if (!empty($l[0]['hoofdtabel'])) {
        $geo->type = "FeatureCollection";
        $geo->attribution = 'Wesselink, H. (2020). Kerkenkaart [Data set]. Retrieved on ' . date("F j, Y") . ', from https://geoplaza.vu.nl/projects/kerken';
        $geo->features = array();
        $n = 0;

        foreach ($l as $row) {
            $geo->features[$n] = new stdClass();
            $geo->features[$n]->type = "Feature";
            $geo->features[$n]->geometry = new stdClass();
            $geo->features[$n]->properties = new stdClass();
            $geo->features[$n]->geometry->type = "Point";
            $geo->features[$n]->geometry->coordinates[0] = (double)$row['lokatie'][0]['lon'];
            $geo->features[$n]->geometry->coordinates[1] = (double)$row['lokatie'][0]['lat'];

            foreach (['naam', 'denominatie', 'architect', 'bronnen'] as $item) {
                $geo->features[$n]->properties = arrValues($item, $geo->features[$n]->properties, $row[$item]);
            }
            foreach ($row['hoofdtabel'][0] as $key => $value) {
                $geo->features[$n]->properties->{$key} = isset($value) ? $value : '';
            }

            $geo->features[$n]->properties->bag_pand_id = $row['bag_pand_id'][0]['pand_id'];
            $geo->features[$n]->properties->streetview = 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' . urlencode((double)$row['lokatie'][0]['lat'] . ',' . (double)$row['lokatie'][0]['lon']);
            $n++;
        }
    }
    return $geo;
}

Function createPlaceGeoJSON($l, $col, $placelist)
{
    $plaats_coord = [];
    foreach ($placelist as $r) {
        preg_match_all("/POINT\((\S*)\s(\S*)\)/", $r['centroide_ll'], $matches);
        //error_log(print_r($matches));
        $plaats_coord[$r['naam']]['lon'] = $matches[1][0];
        $plaats_coord[$r['naam']]['lat'] = $matches[2][0];
    }

    global $stijlen, $denominaties;
    $geo = new stdClass();
    $geo->type = "FeatureCollection";
    $geo->attribution = 'Wesselink, H. (2020). Kerkenkaart [Data set]. Retrieved on ' . date("F j, Y") . ', from https://geoplaza.vu.nl/projects/kerken';
    $geo->features = array();
    $n = 0;
    $data = [];
    foreach ($l as $row) {
        $plaats = $row[$col];
        $denominatie_column = 'denominatie_laatst'; // or 'denominatie' slightly different queries
        if ($row[$denominatie_column] == 'Nederlandse Protestanten Bond') {
            $row[$denominatie_column] = 'Nederlandse Protestantenbond';
        }
        if (!in_array($row[$denominatie_column], $denominaties)) {
            $row[$denominatie_column] = 'Overig';
        }
        if ($row['huidige_bestemming'] != 'kerk') {
            $row['huidige_bestemming'] = 'anders';
        }
        if ($row['stijl'] == "classisisme") { //typo
            $row['stijl'] = "classicisme";
        }
        if (!in_array($row['stijl'], $stijlen)) {
            $row['stijl'] = 'overig';
        }
        $categories = ['type', 'periode', 'huidige_bestemming', 'monument', $denominatie_column, 'stijl'];

        foreach ($categories as $c) {
            if (!isset($data[$plaats])) {
                $data[$plaats] = [];
                $data[$plaats][$c] = [];
            }
            if (!isset($data[$plaats][$c][$row[$c]])) {
                $data[$plaats][$c][$row[$c]] = 1;
            } else {
                $data[$plaats][$c][$row[$c]]++;
            }
        }
    }
    foreach ($data as $plaats => $val) {
        $geo->features[$n] = new stdClass();
        $geo->features[$n]->type = "Feature";
        $geo->features[$n]->geometry = new stdClass();
        $geo->features[$n]->properties = new stdClass();
        $geo->features[$n]->geometry->type = "Point";
        $geo->features[$n]->geometry->coordinates[0] = $plaats_coord[$plaats]['lon'];
        $geo->features[$n]->geometry->coordinates[1] = $plaats_coord[$plaats]['lat'];
        $geo->features[$n]->properties->plaats = $plaats;

        foreach ($categories as $c) {
            $t = new stdClass();
            $t->cat = [];
            $t->cnt = [];
            foreach ($val[$c] as $cat => $cnt) {
                array_push($t->cat, $cat);
                array_push($t->cnt, $cnt);
            }

            if ($c == 'type') {
                $tmp = 'vorm';
            } else {
                $tmp=$c;
            }
            $geo->features[$n]->properties->{$tmp} = $t;
        }
        $n++;
    }
    return $geo;
}

