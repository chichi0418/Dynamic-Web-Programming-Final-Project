function getClosingPrice(event) {
    event.preventDefault();
    
    const result = document.getElementById("result");
    result.innerHTML = "";

    const name = document.getElementById("stock-name").value;
    console.log(name);
    
    const formData = new URLSearchParams({
        name: name,
    });
    
    fetch("../backend/stock_api.php", {
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
            result.innerHTML = name + "收盤價：" + data.price;
        } else {
            console.log(data.message);
            if (data.message === "Stock name not supported.") {
                result.innerHTML = "不支援此股票！";
            } else {
                result.innerHTML = "查詢失敗，請再試一次！";
            }
        }
    })
    .catch(error => {
        console.log(error);
    })

}