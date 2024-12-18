<?php
    /*
        HTTP request method: POST
        
        From frontend (Content-Type: application/x-www-form-urlencoded):
        "from_currency"
        "to_currency"
            
        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error
            "message": (string) error message
            "exchange_rate": (double)
        ]        
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    $from_currency = $_POST['from_currency'] ?? null;
    $to_currency = $_POST['to_currency'] ?? null;
    
    if (!$from_currency || !$to_currency) {           
        http_response_code(400);     
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters.",
        ]);
        exit;
    }
    
    $url = "https://tw.rter.info/capi.php";
    $response = file_get_contents($url);

    if ($response === false) {        
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch exchange rate.",
        ]);
        exit;
    }

    $data = json_decode($response, true);

    if ($data === null) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid response from stock API.",
        ]);
        exit;
    }

    if ($from_currency === $to_currency) {
        http_response_code(200);
        $exchange_rate = (double)1;
    } else if ($from_currency === 'USD') {
        if (!isset($data['USD' . $to_currency]['Exrate'])) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Currency not supported: $from_currency.",
            ]);
            exit;
        }
        $exchange_rate = $data['USD' . $to_currency]['Exrate'];
    } else if ($to_currency == 'USD') {
        if (!isset($data['USD' . $from_currency]['Exrate'])) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Currency not supported: $from_currency.",
            ]);
            exit;
        }
        $exchange_rate = 1 / $data['USD' . $from_currency]['Exrate'];
    } else {
        if (!isset($data['USD' . $from_currency]['Exrate'])) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Currency not supported: $from_currency.",
            ]);
            exit;
        }
        
        if (!isset($data['USD' . $to_currency]['Exrate'])) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Currency not supported: $to_currency.",
            ]);
            exit;
        }
        $usd_from = $data['USD' . $from_currency]['Exrate'];
        $usd_to = $data['USD' . $to_currency]['Exrate'];
        $exchange_rate = $usd_to / $usd_from;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "exchange_rate" => $exchange_rate,
    ]);
?>
