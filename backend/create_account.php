<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "name"
        "balance"
        "user"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    require_once './connection.php';

    // Get data from POST
    $name = $_POST['name'] ?? null;
    $balance = isset($_POST['balance']) ? (int)$_POST['balance'] : null;
    $user = $_POST['user'] ?? null;
    $role = "owner";

    if ($name === null || $balance === null || $user === null) {
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

    // Create account
    $query = "INSERT INTO `accounts` (`name`, `balance`) VALUES (:name, :balance)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':balance', $balance, PDO::PARAM_INT);
    $stmt->execute();
    $lastId = (int)$pdo->lastInsertId();

    // Add the newly created account to user_accounts table
    $query = "INSERT INTO `user_accounts` (`user_id`, `account_id`, `role`) VALUES (:user_id, :account_id, :role)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':account_id', $lastId, PDO::PARAM_INT);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->execute();
    http_response_code(201);
    echo json_encode([
        "status" => "success",
    ]);
?>
