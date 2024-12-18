<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "transaction_id"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    header('Content-Type: application/json; charset=UTF-8');
    
    require_once './connection.php';

    $transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : null;

    if ($transaction_id === null) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    try {
        // Get amount, account, and to_account of the transaction
        $query = "SELECT `amount`, `account`, `to_account` FROM `transactions` WHERE `id` = :transaction_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
        $stmt->execute();

        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transaction) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Transaction not found."
            ]);
            exit;
        }
        
        $amount = $transaction['amount'];
        $account = $transaction['account'];
        $to_account = $transaction['to_account'];

        // Delete the transaction
        $query = "DELETE FROM `transactions` WHERE `id` = :transaction_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Update the balance of the source account
            $query = "UPDATE `accounts` SET `balance` = `balance` - :amount WHERE `id` = :account_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindParam(':account_id', $account, PDO::PARAM_INT);
            $stmt->execute();

            // Update the balance of the destination account (if provided)
            if ($to_account) {
                $query = "UPDATE `accounts` SET `balance` = `balance` + :amount WHERE `id` = :account_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
                $stmt->bindParam(':account_id', $to_account, PDO::PARAM_INT);
                $stmt->execute();
            }

            $pdo->commit();

            http_response_code(200);
            echo json_encode([
                "status" => "success",
            ]);
        } else {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Transaction not found.",
            ]);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage(),
        ]);
    }
?>
