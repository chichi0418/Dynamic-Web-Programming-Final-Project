<?php
// 假設 $monthly_budget 是每月預算，$total_spent 是已經支出的金額，$days_left 是剩餘天數
function calculateDailyBudget($monthly_budget, $total_spent, $days_left) {
    $daily_budget = $monthly_budget / 30;
    $remaining_daily_budget = ($monthly_budget - $total_spent) / $days_left;
    return ['daily_budget' => $daily_budget, 'remaining_daily_budget' => $remaining_daily_budget];
}

// 範例資料
$monthly_budget = 30000; // 每月預算 30,000 元
$total_spent = 12000; // 截至目前已支出 12,000 元
$days_left = 15; // 剩餘 15 天

$result = calculateDailyBudget($monthly_budget, $total_spent, $days_left);
echo "每日預算: " . $result['daily_budget'] . " 元\n";
echo "剩餘每日預算: " . $result['remaining_daily_budget'] . " 元\n";

function checkOverBudget($amount_spent_today, $daily_budget) {
    if ($amount_spent_today > $daily_budget) {
        return "警告！今天的支出超過了預算！";
    }
    return "今天的支出在預算範圍內。";
}

// 範例
$amount_spent_today = 1200; // 今天已支出 1,200 元
$daily_budget = $result['daily_budget']; // 從計算中獲得每日預算

echo checkOverBudget($amount_spent_today, $daily_budget);

?>