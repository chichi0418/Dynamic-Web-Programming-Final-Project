function createTransaction(event) {
    event.preventDefault();

    const user = sessionStorage.getItem("username");
    const account = sessionStorage.getItem("username");
    let amount_ = document.getElementById("amount").value;
    const description = document.getElementById("reason").value;
    const type = document.getElementById("type").value;
    let category_;
    if (type === "expense") {
        const categoryValue = document.querySelector('input[name="category"]:checked')?.value;
        switch (categoryValue) {
            case 'food':
                category_ = "食";
                break;
            case 'clothing':
                category_ = "衣";
                break;
            case 'housing':
                category_ = "住";
                break;
            case 'transportation':
                category_ = "行";
                break;
            case 'education':
                category_ = "育";
                break;
            case 'entertainment':
                category_ = "樂";
                break;
        }
        amount_ *= -1;
    } else if (type === "income") {
        category_ = "收入";
    }
    const amount = amount_;
    const category = category_;
    const time = document.getElementById('date').value;

    if (!amount || !description || !type || !category || !time) {
        alert("請填寫完整資訊!");
        return;
    }

    const formData = new URLSearchParams({
        description: description,
        amount: amount,
        category: category,
        time: time,
        user: user,
        account: account,
    });

    fetch("../backend/create_transaction.php", {
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
            alert("新增成功！");
            window.location.href = "./main.html";
        } else {
            console.log(data.message);
            alert("新增失敗，請再試一次！");
        }
    })
    .catch(error => {
        console.log(error);
        alert("新增失敗，請再試一次！");
    })
}

function confirmTransfer(event) {
    event.preventDefault();

    const confirmation = confirm("是否確定匯款？");
    if (confirmation) {
        const user = sessionStorage.getItem("username");
        const account = sessionStorage.getItem("username");
        const amount = -document.getElementById("amount").value;
        const password = document.getElementById("payer-password").value;
        const to_user = document.getElementById("recipient-account").value;
        const to_account = document.getElementById("recipient-account").value;
        const description = document.getElementById("description").value;
        const category = "匯款";
    
        if (!amount || !password || !to_user || !to_account || !description) {
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
            user: user,
            account: account,
            amount: amount,
            password: password,
            to_user: to_user,
            to_account: to_account,
            description: description,
            category: category,
            time: time,
        });
    
        fetch("../backend/create_transaction.php", {
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
                    console.log(data.message);
                    alert("匯款失敗，請再試一次！");
                }                
            }
        })
        .catch(error => {
            console.log(error);
            alert("匯款失敗，請再試一次！");
        })
    } else {
        alert("匯款已取消！");
    }
}
