<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "transaction_id"
        "description"
        "amount"
        "category"
        "user_id"
        "account_id"
        "time"
        "to_user"
        "to_account"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    require_once './connection.php';

    // Get data from POST
    $transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : null;
    $description = $_POST['description'] ?? null;
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : null;
    $category = $_POST['category'] ?? null;
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $account_id = isset($_POST['account_id']) ? (int)$_POST['account_id'] : null;
    $time = $_POST['time'] ?? null;
    $to_user = $_POST['to_user'] ?? null;
    $to_account = $_POST['to_account'] ?? null;

    // Validate required fields
    if ($transaction_id === null || $description === null || $amount === null || $category === null || $user_id === null || $account_id === null || $time === null) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters.",
        ]);
        exit;
    }

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
                "message" => "to_user not found.",
            ]);
            exit;
        }
        $to_user_id = $result['id'];
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
                "message" => "to_account not found.",
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
            "message" => "Invalid time format.",
        ]);
        exit;
    }

    // Get original amount, account, and to_account of the transaction
    $query = "SELECT `amount`, `account`, `to_account` FROM `transactions` WHERE `id` = :transaction_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();

    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$transaction) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Transaction not found.",
        ]);
        exit;
    }
        
    $original_amount = $transaction['amount'];
    $original_account_id = $transaction['account'];
    $original_to_account_id = $transaction['to_account'];

    $pdo->beginTransaction();

    // Update transaction
    try {
        $query = "UPDATE `transactions` 
                  SET 
                    `description` = :description,
                    `amount` = :amount,
                    `category` = :category,
                    `user` = :user_id,
                    `account` = :account_id,
                    `time` = :time,
                    `to_user` = :to_user,
                    `to_account` = :to_account
                  WHERE `id` = :transaction_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
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
        
        if ($stmt->rowCount() == 0) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Transaction not found or no changes were made.",
            ]);
            exit;
        }

        // Update account balances
        try {
            // Update source account balance
            if ($account_id === $original_account_id) {
                $query = "UPDATE `accounts` SET `balance` = `balance` - :original_amount + :amount WHERE `id` = :account_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':original_amount', $original_amount, PDO::PARAM_INT);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $query = "UPDATE `accounts` SET `balance` = `balance` - :original_amount WHERE `id` = :account_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':original_amount', $original_amount, PDO::PARAM_INT);
                $stmt->bindParam(':account_id', $original_account_id, PDO::PARAM_INT);
                $stmt->execute();
        
                $query = "UPDATE `accounts` SET `balance` = `balance` + :amount WHERE `id` = :account_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            // Update destination account balance
            if ($to_account_id !== null) {
                if ($to_account_id === $original_to_account_id) {
                    $query = "UPDATE `accounts` SET `balance` = `balance` + :original_amount - :amount WHERE `id` = :account_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':original_amount', $original_amount, PDO::PARAM_INT);
                    $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                    $stmt->bindParam(':account_id', $to_account_id, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    $query = "UPDATE `accounts` SET `balance` = `balance` + :original_amount WHERE `id` = :account_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':original_amount', $original_amount, PDO::PARAM_INT);
                    $stmt->bindParam(':account_id', $original_to_account_id, PDO::PARAM_INT);
                    $stmt->execute();
            
                    $query = "UPDATE `accounts` SET `balance` = `balance` - :amount WHERE `id` = :account_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                    $stmt->bindParam(':account_id', $to_account_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            $pdo->commit();

            http_response_code(200);
            echo json_encode([
                "status" => "success",
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
            exit;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage(),
        ]);
        exit;
    }
?>
