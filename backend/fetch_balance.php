<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user = $input['username'] ?? null;

if (!$user) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "缺少必要參數"]);
    exit;
}

try {
    $query = "SELECT balance FROM accounts WHERE name = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $user, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "用戶不存在"
        ]);
        exit;
    }

    echo json_encode(["status" => "success", "balance" => $result['balance']]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "資料庫查詢失敗"]);
}
?>
