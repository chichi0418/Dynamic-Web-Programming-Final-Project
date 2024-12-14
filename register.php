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

    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing username or password.",
        ]);
        exit;
    }

    require_once './connection.php';

    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);


    $query = "SELECT COUNT(*) FROM `users` WHERE `username` = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode([
            "status" => "error",
            "message" => "Username already exists.",
        ]);
    } else {
        $hased_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $query = "INSERT INTO `users` (`username`, `password`) VALUES (:username, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hased_password, PDO::PARAM_STR);
            $stmt->execute();
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Register successfully.",
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
            exit;
        }
    }
?>
