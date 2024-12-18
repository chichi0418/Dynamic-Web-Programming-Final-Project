<?php
    /*
        HTTP request method: POST
        
        From frontend (Content-Type: application/x-www-form-urlencoded):
        "name"
            
        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error
            "message": (string)
            "price": (double)
        ]
    */
    
    header('Content-Type: application/json; charset=UTF-8');

    $name = $_POST['name'] ?? null;
    
    if (!$name) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required parameters.",
        ]);
        exit;
    }
    
    $url = "https://openapi.twse.com.tw/v1/exchangeReport/STOCK_DAY_ALL";
    $response = file_get_contents($url);

    if ($response === false) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch stock data.",
        ]);
        exit;
    }

    $data = json_decode($response, true);

    $key = array_search($name, array_column($data, 'Name'));

    if ($key !== false) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "price" => (double)$data[$key]['ClosingPrice'],
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Stock name not supported.",
        ]);
    }

?>
