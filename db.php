<?php
session_start();
require_once __DIR__ . '/../../pinclude/.secrets/dbConn.php';  // defines $mysqli

/*
    takes in sql string and the string parameters for a prepared statement
*/
function query($sql, ...$params) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return $stmt->affected_rows;
}

// when user type something use this before print
function e($text) {
    return htmlspecialchars($text);
}