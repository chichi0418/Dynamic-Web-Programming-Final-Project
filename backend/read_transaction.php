<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "time"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
            "result": (array),
        ]
    */
    
    session_start();

    header('Content-Type: application/json; charset=UTF-8');
    
    require_once './connection.php';

    $user = $_POST['user'] ?? null;
    $time = $_POST['time'] ?? null;

    $datetime = date('Y-m-d H:i:s', strtotime($time));
    if (!$datetime) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid time format."
        ]);
        exit;
    }

    if ($user === null || $time === null) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters."
        ]);
        exit;
    }
    
    // Get user id
    $user_id = null;
    $query = "SELECT `id` FROM `users` WHERE `username` = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $user, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "user not found."
        ]);
        exit;
    }
    $user_id = $result['id'];
    
    
    $query = "SELECT `amount`, `description`, `category`
                FROM `transactions`
                WHERE `time` = :time AND `user` = :user_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':time', $datetime, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "result" => $result,
    ]);
?>
