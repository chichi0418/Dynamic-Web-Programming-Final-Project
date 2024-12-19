const urlParams = new URLSearchParams(window.location.search);

let month = urlParams.get('month');
let day = urlParams.get('day');
month = month.padStart(2, '0');
day = day.padStart(2, '0');

const time = `2024-${month}-${day} 00:00:00`;

const formData = new URLSearchParams({
    time: time,
});

fetch("../backend/read_transaction.php", {
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
        console.log(data.result);

        const incomeTable = document.createElement('table');
        let headerRow = incomeTable.insertRow();
        let headers = ['理由', '金額'];
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        data.result.forEach(item => {
            if (item.category === '收入') {
                const row = incomeTable.insertRow();
                
                const descriptionCell = row.insertCell();
                descriptionCell.textContent = item.description;
    
                const amountCell = row.insertCell();
                amountCell.textContent = item.amount;
            }
        });
        const income = document.getElementById("income");
        income.appendChild(incomeTable);

        
        const expenseTable = document.createElement('table');
        headerRow = expenseTable.insertRow();
        headers = ['理由', '類型','金額'];
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        data.result.forEach(item => {
            if (item.category !== '收入') {
                const row = expenseTable.insertRow();
                
                const descriptionCell = row.insertCell();
                descriptionCell.textContent = item.description;

                const categoryCell = row.insertCell();
                categoryCell.textContent = item.category;
    
                const amountCell = row.insertCell();
                amountCell.textContent = -item.amount;
            }
        });
        const expense = document.getElementById("expense");
        expense.appendChild(expenseTable);

    } else {
        console.log(data.message);
    }
})
.catch(error => {
    console.log(error);
})
