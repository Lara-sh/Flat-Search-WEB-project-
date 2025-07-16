<?php
define('DBHOST', 'localhost');
define('DBNAME', '1220063');
define('DBUSER', 'root');
define('DBPASS', '');

function db_connect($dbhost = DBHOST, $dbname = DBNAME, $username = DBUSER, $password = DBPASS) {
    try {
        $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        return $pdo;
    } catch (PDOException $ex) {
        die("Database connection failed: " . $ex->getMessage());
    }
}
?>
