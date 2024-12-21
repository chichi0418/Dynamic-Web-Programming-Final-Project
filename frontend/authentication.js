function signup(event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const message = document.getElementById("message");

    if (!username || !password) {
        message.innerHTML = "請輸入帳號以及密碼！"
        return;
    }

    const formData = new URLSearchParams({
        username: username,
        password: password,
    });

    fetch("../backend/register.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            message.innerHTML = "註冊成功！";

            // 顯示提示視窗，讓用戶輸入起始餘額
            const initialBalance = prompt("請輸入起始餘額（數字）：");
            
            if (initialBalance !== null && !isNaN(initialBalance) && initialBalance >= 0) {
                const balanceData = new URLSearchParams({
                    username: username,
                    balance: initialBalance,
                });

                // 傳送起始餘額到後端儲存
                fetch("../backend/update_balance.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: balanceData.toString()
                })
                .then(balanceResponse => balanceResponse.json())
                .then(balanceResult => {
                    if (balanceResult.status === "success") {
                        alert("起始餘額已成功設定！");
                        setTimeout(() => {
                            window.location.href = "login.html"; // 跳轉到 login.html
                        }, 800);
                    } else {
                        alert(balanceResult.message || "設定餘額失敗，請稍後再試！");
                    }
                })
                .catch(() => {
                    alert("設定餘額時發生錯誤，請稍後再試！");
                });
            } else {
                alert("未輸入有效的起始餘額，請稍後進入系統手動設定！");
                setTimeout(() => {
                    window.location.href = "login.html"; // 跳轉到 login.html
                }, 800);
            }
        } else {
            message.innerHTML = data.message;
        }
    })
    .catch(() => {
        message.innerHTML = "註冊失敗，請再試一次！";
    });
}


function login(event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const message = document.getElementById("message");

    if (!username || !password) {
        message.innerHTML = "請輸入帳號以及密碼！"
        return
    }

    const formData = new URLSearchParams({
        username: username,
        password: password,
    });

    fetch("../backend/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: formData.toString()
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            message.innerHTML = "登入成功！";
            // 等 0.8 秒後跳進系統主頁面
            setTimeout(() => {
                window.location.href = "main.html"; // 跳轉到 main.html
            }, 800); // 等待 800 毫秒 (0.8 秒)
        } else {
            message.innerHTML = data.message;
        }
    })
    .catch(error => {
        message.innerHTML = "登入失敗，請再試一次！"
    })
}
