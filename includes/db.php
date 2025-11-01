<?php
    $host = "localhost";
    $user = "root";
    $pass = "Nitesh#@?789";
    $dbname = "niteshdb";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
?>
