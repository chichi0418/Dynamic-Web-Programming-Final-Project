<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=UTF-8');

    if (!isset($_POST['username']) || !isset($_POST['balance'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "缺少必要參數！"
        ]);
        exit;
    }

    require_once './connection.php';

    $username = htmlentities($_POST['username']);
    $balance = floatval($_POST['balance']);

    if ($balance < 0) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "餘額必須是非負數！"
        ]);
        exit;
    }

    try {
        $query = "UPDATE `accounts` SET `balance` = :balance WHERE `name` = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':balance', $balance, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "餘額已成功更新！"
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "用戶不存在或更新失敗！"
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "更新餘額時發生錯誤！"
        ]);
    }
?>
