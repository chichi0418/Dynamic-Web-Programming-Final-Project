<?php

function transferMoney($from_user_id, $to_user_id, $amount, $currency_from, $currency_to) {
    // 獲取匯率（可以透過 API 獲取實時匯率）
    $exchange_rate = getExchangeRate($currency_from, $currency_to);

    // 計算轉帳金額
    $converted_amount = $amount * $exchange_rate;

    // 進行資料庫更新，將轉帳交易記錄到 transactions 表格
    $sql = "INSERT INTO transactions (user_id, description, amount, category, date) 
            VALUES (?, 'Transfer to user $to_user_id', ?, 'Transfer', NOW())";
    
    // 使用 Prepared Statements 來防止 SQL 注入
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$from_user_id, -$converted_amount]);  // 扣除金額
    $stmt->execute([$to_user_id, $converted_amount]);  // 收到金額
    
    return "轉帳成功！";
}

// 假設匯率 API
function getExchangeRate($currency_from, $currency_to) {
    // 假設這裡有一個 API 來獲取即時匯率
    $api_url = "https://api.exchangerate-api.com/v4/latest/$currency_from";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    return $data['rates'][$currency_to];
}

?>