<?php
    function sanitize($conn, $value) {
        // this function Trim extra spaces, remove backslashes, convert HTML entities
        return mysqli_real_escape_string($conn, trim($value));
    }

    function sendResponse($status, $message) {
        echo json_encode(["status" => $status, "message" => $message]);
        exit;
    }
?>