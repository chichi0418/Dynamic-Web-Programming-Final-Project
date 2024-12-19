<?php
    session_start();

    header('Content-Type: application/json; charset=UTF-8');
    
    $username = $_SESSION['username'];

    http_response_code(200);
    echo json_encode([
        "username" => $username,
    ]);
?>