<?php
    // deal with CORS（跨來源資源共享） problem
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    $host = '127.0.0.1';    # modifed
    $data = 'accounting';
    $user = 'root';
    $pass = 'root';     # modifed
    $chrs = 'utf8mb4';
    $attr = "mysql:host=$host;port=8890;dbname=$data;charset=$chrs";    # added port=8890
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
            "message" => $e->getMessage(),
        ]);
        exit;
    }
?>
