function createTransaction(event) {
    event.preventDefault();

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
    });

    const message = document.getElementById("message");

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
            message.innerHTML = "新增成功！";
            window.location.href = "./main.html";
        } else {
            message.innerHTML = "新增失敗，請再試一次！";
            setTimeout(() => {
                window.location.href = "./main.html";
            }, 800);
        }
    })
    .catch(error => {
        message.innerHTML = "新增失敗，請再試一次！";
    })
}
