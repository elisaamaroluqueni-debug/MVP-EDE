<?php
function db(): PDO {
    static $p = null;
    if (!$p) {
        $p = new PDO('mysql:host=localhost;dbname=investtrack_db;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $p;
}
