<?php

// Insert data into db
//$sql = new mysqli("localhost", "root", "", "roosters");
//$sql->autocommit(false);
//
//foreach ($days as $day) {
//    foreach ($day as $period) {
//        if (!$sql->query("INSERT IGNORE INTO subjects (name) VALUES ('$period[0]')")) {
//            echo $sql->error . "<br />";
//        }
//        
//        if (!$sql->query("INSERT IGNORE INTO teachers (name) VALUES ('$period[1]')")) {
//            echo $sql->error . "<br />";
//        }
//        
//        if (!$sql->query("INSERT IGNORE INTO rooms (name) VALUES ('$period[2]')")) {
//            echo $sql->error . "<br />";
//        }
//    }
//}
//
//if (!$sql->query("COMMIT")) {
//    echo $sql->error;
//}
//
//$sql->close();

ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);

include_once("./Rosters.php");
include_once("./Parser.php");
include_once("./data/Roster.php");
include_once("./data/Day.php");
include_once("./data/Period.php");

$r = new Rosters();
$r->debug = true;
$r->synchronize();
