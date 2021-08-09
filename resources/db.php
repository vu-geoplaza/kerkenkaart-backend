<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
include 'config.php';

define('DB_VIEW','vw_viewer_data');

/**
 * Description of db
 *
 * @author peter
 */
class db {

    function __construct() {
        // pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass
        $this->dbh = new PDO("pgsql:host=" . DBHOST . ";port=" . DBPORT . ";dbname=" . DB . ";user=" . DBUSER . ";password=" . DBPW) or die('connection failed');
    }

    Function getFiltered($filter) {
        /* Available filters:
         * Plaats
         * Naam
         * Architect
         * 
         * Denominatie
         * Stijl
         * Type
         * 
         * $filter['<filter>'] = Array of requested values
         */
        $filter_options = ['plaats', 'denominatie', 'stijl', 'vorm', 'monument', 'periode', 'gemeente', 'provincie'];
        $sql = 'SELECT * FROM '.DB_VIEW.' WHERE';
        foreach ($filter_options as $field) {
            if (isset($filter[$field])&&count($filter[$field])>0) {
                if ($field==='denominatie') {
                    $f = 'denominatie_laatst';
                } elseif ($field==='vorm') {
                    $f = 'type';
                } else {
                    $f=$field;
                }
                $s = implode('\',\'', $filter[$field]);
                $sql .= ' ' . $f . ' IN (\'' . $s . '\') AND';
            }
        }
        if (isset($filter['huidige_bestemming'])&&count($filter['huidige_bestemming'])>0) {
            if ($filter['huidige_bestemming'] == ['kerk']) {
                $sql .= ' huidige_bestemming=\'kerk\' AND';
            } else {
                $sql .= ' huidige_bestemming<>\'kerk\' AND';
            }
        }

        $ids = array();
        if (isset($filter['architect'])&&count($filter['architect'])>0) {
            $this->dbh->exec("SET CHARACTER SET utf8");
            $a = str_replace('&amp;', '&', implode('\',\'', $filter['architect']));
            $sqla = 'SELECT "ID" from "013_Architect" WHERE "Architect" IN (\'' . $a . '\')';
            $sth = $this->dbh->prepare($sqla);
            $sth->execute();
            $id_s = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
            $sql .= ' id IN (' . implode(',', $id_s) . ') AND';
        }

        if (isset($filter['naam'])&&count($filter['naam'])>0) {
            $this->dbh->exec("SET CHARACTER SET utf8");
            $a = implode('\',\'', $filter['naam']);
            $sqla = 'SELECT "ID" from "011_Naam_Kerk" WHERE "Naam_Kerk" IN (\'' . $a . '\')';
            $sth = $this->dbh->prepare($sqla);
            $sth->execute();
            $id_s = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
            $sql .= ' id IN (' . implode(',', $id_s) . ') AND';
        }

        $sql = substr($sql, 0, -4) . ' ORDER BY plaats, naam';
        error_log($sql);
        $this->dbh->exec("SET CHARACTER SET utf8");
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    Function getAll() {
        $this->dbh->exec("SET CHARACTER SET utf8");
        $sql = "SELECT * from ".DB_VIEW." ORDER BY plaats, naam";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    function getGemeenten() {
        $this->dbh->exec("SET CHARACTER SET utf8");
        $sql = "SELECT * from ngr_gemeente";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $res = $sth->fetchAll();
        return $res;
    }

    function getProvincies() {
        $this->dbh->exec("SET CHARACTER SET utf8");
        $sql = "SELECT * from ngr_provincie";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $res = $sth->fetchAll();
        return $res;
    }

    function getInfoByPandId($pand_id){
        $sql = "SELECT id FROM ngr_locatie_latlon WHERE pand_id=:pand_id";
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":pand_id" => $pand_id));
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->getInfo($res['id']);
    }

    function getInfo($id) {
        $this->dbh->exec("SET CHARACTER SET utf8");
        $sql = 'SELECT * FROM "01_Hoofdtabel_Kerken" WHERE "ID"=:id';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['hoofdtabel'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT * FROM "011_Naam_Kerk" WHERE "ID"=:id ORDER BY "Van" ASC';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['naam'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT * FROM "012_Denominatie" WHERE "ID"=:id ORDER BY "Van" ASC';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['denominatie'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT * FROM "013_Architect" WHERE "ID"=:id';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['architect'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT * FROM "014_Bronnen" WHERE "ID"=:id ORDER BY "Jaar_van_Uitgave" DESC';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['bronnen'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT lon,lat FROM vw_viewer_data WHERE id=:id';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['lokatie'] = $sth->fetchAll(PDO::FETCH_ASSOC);        
        
        $sql = 'SELECT pand_id FROM ngr_locatie_latlon WHERE id=:id';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(":id" => $id));
        $res['bag_pand_id'] = $sth->fetchAll(PDO::FETCH_ASSOC);        
        return $res;
    }

    function isFilterSet($filter) {
        foreach ($filter as $key => $val) {
            error_log($key);
            error_log(count($val));
            if (count($val) > 0) {
                return True;
            }
        }
        return False;
    }

    function getTypeaheadList($filter, $select) {
        if ($this->isFilterSet($filter)) {
            $list = $this->getFiltered($filter);
            $ids = array();
            $n = 0;
            foreach ($list as $row) {
                $ids[$n] = $row['id'];
                $n++;
            }
            $sql_id = ' WHERE "ID" IN (' . implode(',', $ids) . ')';
            $sql_id_loc = ' WHERE id IN (' . implode(',', $ids) . ')';
        } else {
            $sql_id = '';
            $sql_id_loc = '';
        }
        if ($select == 'architect') {
            $sql = 'SELECT DISTINCT "Architect" FROM "013_Architect"' . $sql_id. 'ORDER BY "Architect"';
        }
        if ($select == 'plaats') {
            $sql = 'SELECT DISTINCT "Plaats" FROM "01_Hoofdtabel_Kerken"' . $sql_id. 'ORDER BY "Plaats"';
        }
        if ($select == 'naam') {
            $sql = 'SELECT DISTINCT "Naam_Kerk" FROM "011_Naam_Kerk"' . $sql_id. 'ORDER BY "Naam_Kerk"';
        }
        if ($select == 'gemeente') {
            $sql = 'SELECT DISTINCT gemeentenaam FROM ngr_locatie_latlon ' . $sql_id_loc. 'ORDER BY gemeentenaam';
        }
        if ($select == 'provincie') {
            $sql = 'SELECT DISTINCT provincienaam FROM ngr_locatie_latlon ' . $sql_id_loc. 'ORDER BY provincienaam';
        }
        error_log($select);
        error_log($sql);
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $res = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
        return $res;
    }

    function getFilterState($filter) {
        error_log(print_r($filter, true));
        $res['denominatie'] = [];
        $res['stijl'] = [];
        $res['type'] = [];
        $res['monument'] = [];
        $res['huidige_bestemming'] = [];
        $res['periode'] = [];
        if ($this->isFilterSet($filter)) {
            error_log(print_r($filter, true));
            $list = $this->getFiltered($filter);
            foreach ($list as $row) {
                foreach ($res as $opt => $val) {
                    $v = $row[$opt];
                    if ($opt == 'huidige_bestemming') {
                        if ($v!=='kerk'){
                            $v='anders';
                        }
                    }
                    if (!in_array($v, $res[$opt])) {
                        array_push($res[$opt], $v);
                    }
                }
            }
        } else {
            return False;
        }
        return $res;
    }

}

function pretty_json($json) {

    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;

    for ($i = 0; $i <= $strLen; $i++) {

// Grab the next character in the string.
        $char = substr($json, $i, 1);

// Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

// If this character is the end of an element,
// output a new line and indent the next line.
        } else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

// Add the character to the result string.
        $result .= $char;

// If the last character was the beginning of an element,
// output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

function decToDeg($coord, $lat) {
    $ispos = $coord >= 0;
    $coord = abs($coord);
    $deg = floor($coord);
    $coord = ($coord - $deg) * 60;
    $min = floor($coord);
    $sec = floor(($coord - $min) * 60);
    if ($lat) {
        $c = sprintf("%d&deg;%d'%d\"%s", $deg, $min, $sec, $ispos ? 'N' : 'S');
    } else {
        $c = sprintf("%d&deg;%d'%d\"%s", $deg, $min, $sec, $ispos ? 'E' : 'W');
    }
    return $c;
}

function pdo_debugStrParams($stmt) {
  ob_start();
  $stmt->debugDumpParams();
  $r = ob_get_contents();
  ob_end_clean();
  return $r;
}
