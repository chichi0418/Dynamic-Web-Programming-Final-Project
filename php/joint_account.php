<?php

// CREATE TABLE `accounts` (
//     `account_id` INT AUTO_INCREMENT PRIMARY KEY,
//     `account_name` VARCHAR(255) NOT NULL,
//     `owner_id` INT, -- 帳戶所有者
//     `balance` DECIMAL(10, 2) DEFAULT 0.00,
//     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

// CREATE TABLE `account_members` (
//     `account_id` INT,
//     `user_id` INT,
//     `role` ENUM('admin', 'member') DEFAULT 'member', -- 角色：管理員或成員
//     PRIMARY KEY (`account_id`, `user_id`),
//     FOREIGN KEY (`account_id`) REFERENCES accounts(`account_id`),
//     FOREIGN KEY (`user_id`) REFERENCES users(`id`)
// );


function createSharedAccount($owner_id, $account_name) {
    // 創建一個新的共同帳戶
    $sql = "INSERT INTO accounts (owner_id, account_name, balance) 
            VALUES (?, ?, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$owner_id, $account_name]);
    return "共同帳戶創建成功！";
}

function addMemberToSharedAccount($account_id, $user_id, $role) {
    // 添加成員到共同帳戶
    $sql = "INSERT INTO account_members (account_id, user_id, role) 
            VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$account_id, $user_id, $role]);
    return "成員添加成功！";
}

// 把錢從 from_user_id 轉到 account_id (金額 = amount)
function transferToSharedAccount($account_id, $from_user_id, $amount) {
    // 檢查用戶是否有權限操作
    $sql = "SELECT * FROM account_members WHERE account_id = ? AND user_id = ? AND role IN ('admin', 'member')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$account_id, $from_user_id]);
    $user_role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_role) {
        return "您沒有權限進行此操作！";
    }

    // 進行轉帳
    $sql = "UPDATE accounts SET balance = balance + ? WHERE account_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$amount, $account_id]);

    return "轉帳至共同帳戶成功！";
}

?>