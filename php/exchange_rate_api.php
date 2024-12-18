<?php
    /*
        HTTP request method: POST
        
        From frontend (Content-Type: application/x-www-form-urlencoded):
        "currency"
            
        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error
            "message": (string) error message
            "exchange_rate": (double)
        ]        
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    $currency = $_POST['currency'] ?? null;
    
    if (!$currency) {           
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

    $usdtwd = $data['USDTWD']['Exrate'];

    if ($currency === "USD") {
        $exchange_rate = 1 / $usdtwd;
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "exchange_rate" => $exchange_rate,
        ]);
    } else {
        if (!isset($data['USD' . $currency]['Exrate'])) {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Currency not supported.",
            ]);
            exit;
        }

        $usdcurrency = $data['USD' . $currency]['Exrate'];
        $exchange_rate = $usdcurrency / $usdtwd;
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "exchange_rate" => $exchange_rate,
        ]);
    }
?>
