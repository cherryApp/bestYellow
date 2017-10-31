<?php

// Load config.
require_once("config.php");

// Send error message.
function send_error($title, $message) {
    $cerror = new stdClass;
    $cerror->title = $title;
    $cerror->message = $message;
    exit( json_encode($cerror) );
}

// Connect to the MySQL database.
$dsn = 'mysql:dbname='.DBNAME.';host=127.0.0.1';
try {
    $dbh = new PDO($dsn, DBUSER, DBPASSWORD);
} catch (PDOException $e) {
    // Connection error.
    send_error("Kapcsolódási hiba", $e->getMessage());
}

// Url variables.
$url = $_SERVER['REQUEST_URI'];
/*preg_match_all(
    '/(\:[^\/]*)/',
    $url,
    $matches
);*/

// print_r($matches);

// Parse url.
$path = explode(PREFIX, $url);
$path = $path[1];

// Get sql file.
$sql_path = SQLDIR . DIRECTORY_SEPARATOR;
if (isset($router[$path])) {
    $sql_path .= $router[$path];
} else {
    send_error(
        "Router hiba", 
        "Ehhez az url-hez nincs beállított routing szabály a "
        ."config.php fájlban!"
    );
}

// Read .sql file.
$sql_statement = @file_get_contents($sql_path);
if ($sql_statement === false) {
    send_error("SQL file hiba", "A fájl nem létezik: ".$sql_path);
}

// Run statement.
$stm = $dbh->query($sql_statement, PDO::FETCH_ASSOC);
$res = $stm->fetchAll();

echo '<pre>';
print_r($res);
echo '</pre>';

