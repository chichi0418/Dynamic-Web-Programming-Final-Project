<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "user_id"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
            "result": (array),
        ]
    */

    header('Content-Type: application/json; charset=UTF-8');
    
    require_once './connection.php';

    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    if ($user_id === null) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters."
        ]);
        exit;
    }
    
    $query = "SELECT `t`.`amount`, `t`.`category`, `a`.`name` AS `account`, `t`.`time`, `tu`.`username` AS `to_user`, `ta`.`name` AS `to_account`
              FROM `transactions` `t`
              LEFT JOIN `accounts` `a` ON `t`.`account` = `a`.`id`
              LEFT JOIN `users` `tu` ON `t`.`to_user` = `tu`.`id`
              LEFT JOIN `accounts` `ta` ON `t`.`to_account` = `ta`.`id`";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "result" => $result,
    ]);
?>
