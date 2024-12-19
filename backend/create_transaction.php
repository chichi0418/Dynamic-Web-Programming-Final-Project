<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "description"
        "amount"
        "category"
        "user"
        "account"
        "time"
        "to_user"
        "to_account"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    session_start();

    header('Content-Type: application/json; charset=UTF-8');

    require_once './connection.php';

    // Get data from POST
    $description = $_POST['description'] ?? null;
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : null;
    $category = $_POST['category'] ?? null;
    $user = $_SESSION['username'] ?? null;
    $account = $_SESSION['username'] ?? null;
    $time = $_POST['time'] ?? null;
    $to_user = $_POST['to_user'] ?? null;
    $to_account = $_POST['to_account'] ?? null;
    $password = $_POST['password'];

    // Validate required fields
    if ($description === null || $amount === null || $category === null || $user === null || $account === null || $time === null) {
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

    // Get account id
    $account_id = null;
    $query = "SELECT `accounts`.`id` FROM `accounts` 
              JOIN `user_accounts` ON `accounts`.`id` = `user_accounts`.`account_id` 
              WHERE `user_accounts`.`user_id` = :user_id AND `accounts`.`name` = :account_name";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':account_name', $account, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "account not found."
        ]);
        exit;
    }
    $account_id = $result['id'];
    
    // Verify user and account
    $query = "SELECT COUNT(*) FROM `user_accounts` WHERE `user_id` = :user_id AND `account_id` = :account_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "The account does not belong to the user.",
        ]);
        exit;
    }

    // Get to_user id (if provided)
    $to_user_id = null;
    if ($to_user !== null) {
        $query = "SELECT `id` FROM `users` WHERE `username` = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $to_user, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "to_user not found."
            ]);
            exit;
        }
        $to_user_id = $result['id'];
        $query = "SELECT `password` FROM `users` WHERE `username` = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $to_user, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if (!password_verify($password, $result['password'])) {
                http_response_code(401);
                echo json_encode([
                    "status" => "error",
                    "message" => "密碼錯誤！",
                ]);
                exit;
            }
        }
    }

    // Get to_account id (if provided)
    $to_account_id = null;
    if ($to_user !== null && $to_account !== null) {
        $query = "SELECT `accounts`.`id` FROM `accounts` 
                  JOIN `user_accounts` ON `accounts`.`id` = `user_accounts`.`account_id` 
                  WHERE `user_accounts`.`user_id` = :user_id AND `accounts`.`name` = :account_name";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $to_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_name', $to_account, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "to_account not found."
            ]);
            exit;
        }
        $to_account_id = $result['id'];
    }
    
    // Verify to_user and to_account (if provided)
    if ($to_user_id !== null && $to_account_id !== null) {
        $query = "SELECT COUNT(*) FROM `user_accounts` WHERE `user_id` = :user_id AND `account_id` = :account_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $to_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $to_account_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->fetchColumn() == 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "The to_account does not belong to the to_user.",
            ]);
            exit;
        }
    }

    // Transform datetime
    $datetime = date('Y-m-d H:i:s', strtotime($time));
    if (!$datetime) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid time format."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // Insert transaction
    try {
        $query = "INSERT INTO `transactions` 
          (`description`, `amount`, `category`, `user`, `account`, `time`, `to_user`, `to_account`) 
          VALUES (:description, :amount, :category, :user, :account, :time, :to_user, :to_account)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':user', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':account', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':time', $datetime, PDO::PARAM_STR);
        if ($to_user_id === null) {
            $stmt->bindParam(':to_user', $to_user_id, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':to_user', $to_user_id, PDO::PARAM_INT);
        }
        if ($to_account_id === null) {
            $stmt->bindParam(':to_account', $to_account_id, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':to_account', $to_account_id, PDO::PARAM_INT);
        }
        $stmt->execute();

        // Update account balances
        try {
            // Update source account balance
            $query = "UPDATE `accounts` SET `balance` = `balance` + :amount WHERE `id` = :account_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            $stmt->execute();

            // Update destination account balance
            if ($to_account_id !== null) {
                $query = "UPDATE `accounts` SET `balance` = `balance` - :amount WHERE `id` = :account_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                $stmt->bindParam(':account_id', $to_account_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            "status" => "success",
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
        exit;
    }
?>
