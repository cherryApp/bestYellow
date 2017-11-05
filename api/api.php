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
// Get path from the router config.
function getPath($router, $path) {
    if (isset($router[$path])) {
        return array($path, $router[$path]);
    } else {
        $keys = array_keys($router);
        foreach($keys as $key) {
            $alt_key = preg_replace('/\:[^\/]*/', '[^/]*', $key);
            $alt_key = str_replace('/', '\\/', $alt_key);
            preg_match('/^'.$alt_key.'$/', $path, $matches);
            if (count($matches) > 0) {
                return array($key, $router[$key]);
            }
        }
        
        return false;
    }
}
// Insert variables to the sql string.
function insertVars($router_result, $url, $sql) {
    // Add post data.
    if (strtolower($_SERVER['REQUEST_METHOD']) == "post") {
        $input = file_get_contents("php://input");
        $input = json_decode($input);
        foreach($input as $key => $value) {
            if (strpos($sql, ':'.$key) !== false) {
                $sql = str_replace(':'.$key, $value, $sql);
            }
        }
    }
    preg_match_all(
        '/(\:[^\/]*)/',
        $router_result[0],
        $keys
    );
    if (count($keys) < 1) {
        return $sql;
    }
    // Add url data.
    $alt_key = preg_replace('/\:[a-z^\/]*/', '([a-z0-9A-Z^/]*)', $router_result[0]);
    $alt_key = str_replace('/', '\\/', $alt_key);
    preg_match('/'.$alt_key.'/', $url, $matches);
    for($i = 0; $i < count($keys[1]); $i++) {
        if (isset($matches[$i+1])) {
            $sql = str_replace($keys[1][$i], $matches[$i+1], $sql);
        }
    }
    return $sql;
}
// Connect to the MySQL database.
$dsn = 'mysql:dbname='.DBNAME.';host=127.0.0.1';
try {
    $dbh = new PDO($dsn, DBUSER, DBPASSWORD);
    $dbh->exec("set names utf8");
} catch (PDOException $e) {
    // Connection error.
    send_error("Kapcsolódási hiba", $e->getMessage());
}
// Url variables.
$url = $_SERVER['REQUEST_URI'];
// Parse url.
$path = explode(PREFIX, $url);
$path = $path[1];

// Get sql file.
$sql_path = SQLDIR . DIRECTORY_SEPARATOR;
$router_result = getPath($router, $path);
if ($router_result !== false) {
    $sql_path .= $router_result[1];
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
$sql_statement = insertVars($router_result, $path, $sql_statement);

// Run statement.
$statement = $dbh->prepare($sql_statement);
$statement->execute();
// Send error.
if ($statement === false) {
    $info = $dbh->errorInfo();
    send_error("Hibás lekérdezés", $info[2]." SQL: ".$sql_statement);
}
// Print result.
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);