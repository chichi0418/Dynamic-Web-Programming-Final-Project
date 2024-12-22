function confirmTransfer() {
    const confirmation = confirm("是否確定匯款？");
    if (confirmation) {
        const amount = document.getElementById("amount").value;
        const password = document.getElementById("payer-password").value;
        const to_user = document.getElementById("recipient-account").value;
        const to_account = document.getElementById("recipient-account").value;
        const description = "";
        const category = "匯款";
    
        if (!amount || !password || !to_user || !to_account) {
            alert("請填寫完整資訊!");
            return;
        }

        const now = new Date();
        now.setHours(0, 0, 0, 0);
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const time = `${year}-${month}-${day} 00:00:00`;
    
        const formData = new URLSearchParams({
            amount: amount,
            password: password,
            to_user: to_user,
            to_account: to_account,
            description: description,
            category: category,
            time: time,
        });
    
        fetch("../backend/transfer.php", {
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
                alert("匯款成功！");
                window.location.href = "./main.html";
            } else {
                if (data.message == "密碼錯誤！") {
                    alert("密碼錯誤！");
                } else {
                    alert("匯款失敗，請再試一次！");
                }                
            }
        })
        .catch(error => {
            alert("匯款失敗，請再試一次！");
        })
    } else {
        alert("匯款已取消！");
    }
}
