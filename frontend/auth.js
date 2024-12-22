function logout() {
    sessionStorage.clear();
    window.location.href = "login.html";
}

const username = sessionStorage.getItem("username");
if (!username) {
    alert("請先登入");
    window.location.href = "login.html";
}
