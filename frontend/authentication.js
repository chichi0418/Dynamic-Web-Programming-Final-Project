function signup(event) {
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

    fetch("../backend/register.php", {
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
            message.innerHTML = "註冊成功！";
        } else {
            message.innerHTML = data.message;
        }
    })
    .catch(error => {
        message.innerHTML = "註冊失敗，請再試一次！"
    })
}

function login() {
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
        } else {
            message.innerHTML = data.message;
        }
    })
    .catch(error => {
        message.innerHTML = "登入失敗，請再試一次！"
    })
}
