<?php
    // deal with CORS（跨來源資源共享） problem
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    $host = '127.0.0.1';
    $data = 'accounting';
    $user = 'root';
    $pass = ''; // yan
    // $pass = 'root'; // chi
    $chrs = 'utf8mb4';
    $attr = "mysql:host=$host;dbname=$data;charset=$chrs"; // yan
    // $attr = "mysql:host=$host;port=8890;dbname=$data;charset=$chrs"; // chi
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($attr, $user, $pass, $opts);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "資料庫連線失敗！s",
        ]);
        exit;
    }
?>
