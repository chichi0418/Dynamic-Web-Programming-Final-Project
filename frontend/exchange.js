let activeInputId = 'from-currency';

function setActiveInput(inputId) {
    activeInputId = inputId;
}

function setCurrency(inputId, currency) {
    document.getElementById(inputId).value = currency;
}

function convertCurrency() {
    const amount = document.getElementById('amount').value;
    const fromCurrency = document.getElementById('from-currency').value;
    const toCurrency = document.getElementById('to-currency').value;

    const result = document.getElementById("result");

    const formData = new URLSearchParams({
        from_currency: fromCurrency,
        to_currency: toCurrency,
    });
    
    fetch("../backend/exchange_rate_api.php", {
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
            const convertedAmount = (amount * data.exchange_rate).toFixed(2);
            result.innerHTML = `${fromCurrency} ${amount} = ${toCurrency} ${convertedAmount}`;

        }
    })
    .catch(error => {

    })
}