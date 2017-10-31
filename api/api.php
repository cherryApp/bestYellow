<?php

// Load config.
require_once("config.php");

// Connect to the MySQL database.
$dsn = 'mysql:dbname='.DBNAME.';host=127.0.0.1';
try {
    $dbh = new PDO($dsn, DBUSER, DBPASSWORD);
} catch (PDOException $e) {
    // Connection error.
    $cerror = new stdClass;
    $cerror->title = "Kapcsolódási hiba";
    $cerror->message = $e->getMessage();
    exit( json_encode($cerror) );
}

$jdata = new stdClass;
$jdata->title = "Üzenet";
$jdata->message = "A kapcsolódás sikeres volt.";
echo json_encode($jdata);

