<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "username"
        "password"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "請輸入帳號以及密碼！",
        ]);
        exit;
    }
    
    require_once './connection.php';
        
    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);

    $query = "SELECT `id`, `password` FROM `users` WHERE `username` = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        if (password_verify($password, $result['password'])) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "密碼錯誤！",
            ]);
        }
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "帳號不存在！",
        ]);
    }
?>
