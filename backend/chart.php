<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// deal with  CORS（跨來源資源共享） problem
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json; charset=UTF-8');

require_once './connection.php';

$user = $_POST['user'] ?? null;
$time = $_POST['time'] ?? null;
$start_date = $time . '-01';
$end_date = date('Y-m-d', strtotime('+1 month', strtotime($start_date)));

if (!isset($_POST['user']) || !isset($_POST['time'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters: user or time"
    ]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}$/', $time)) {
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
$query = "SELECT `id` FROM `users` WHERE `username` = :username";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':username', $user, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    exit;
}
$user_id = $result['id'];

// Query to calculate category totals
$query = "SELECT 
            CASE 
                WHEN `t`.`category` = '匯款' AND `t`.`amount` > 0 THEN 'inboundRemittance'
                WHEN `t`.`category` = '匯款' AND `t`.`amount` < 0 THEN 'outboundRemittance'
                WHEN `t`.`category` = '食' THEN 'food'
                WHEN `t`.`category` = '衣' THEN 'clothing'
                WHEN `t`.`category` = '住' THEN 'housing'
                WHEN `t`.`category` = '行' THEN 'transportation'
                WHEN `t`.`category` = '育' THEN 'education'
                WHEN `t`.`category` = '樂' THEN 'entertainment'
                WHEN `t`.`category` = '薪水' THEN 'salary'
                WHEN `t`.`category` = '獎金' THEN 'bonus'
                WHEN `t`.`category` = '零用錢' THEN 'pocketMoney'
                WHEN `t`.`category` = '投資獲利' THEN 'investment'
            END AS category,
            SUM(`t`.`amount`) AS total
        FROM `transactions` `t`
        WHERE `t`.`time` >= :start_date 
        AND `t`.`time` < :end_date 
        AND `t`.`user` = :user_id
        GROUP BY category;";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
$stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize totals
$result = [
    "food" => 0,
    "clothing" => 0,
    "housing" => 0,
    "transportation" => 0,
    "education" => 0,
    "entertainment" => 0,
    "income" => 0
];

// Map results into totals
foreach ($transactions as $transaction) {
    $category = $transaction['category'];
    $result[$category] = floatval($transaction['total']);
}

// Return response
http_response_code(200);
echo json_encode([
    "status" => "success",
    "result" => $result
]);
?>
