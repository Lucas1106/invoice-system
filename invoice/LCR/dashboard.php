<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LCR – Financial Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    margin:0;
    background:#0d0d0d;
    font-family: Inter, sans-serif;
    color:white;
}

.wrapper{
    max-width:900px;
    margin:auto;
    padding:20px;
}

/* título */
h1{
    font-size:24px;
    font-weight:600;
    margin-bottom:20px;
}

/* filtros */
.filter-box{
    background:#141414;
    padding:15px;
    border-radius:12px;
    box-shadow:0 0 8px #000;
    margin-bottom:25px;
}
.filter-box label{
    font-size:12px;
}
.filter-box input,
.filter-box select{
    width:100%;
    padding:10px;
    margin:5px 0 12px 0;
    border:none;
    border-radius:8px;
    background:#1f1f1f;
    color:white;
}

button{
    width:100%;
    padding:12px;
    background:#4cc2ff;
    border:none;
    border-radius:10px;
    color:black;
    font-weight:600;
}

/* cards */
.card-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
    gap:12px;
}
.card{
    background:#1a1a1a;
    padding:18px;
    border-radius:12px;
    box-shadow:0 0 6px #000;
}
.card h3{
    color:#4cc2ff;
    font-size:14px;
    margin:0 0 5px 0;
}
.card span{
    font-size:20px;
    font-weight:700;
}

/* tabela */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
    background:#111;
    border-radius:12px;
    overflow:hidden;
}
th{
    background:#1b1b1b;
    padding:12px;
}
td{
    padding:10px;
    border-bottom:1px solid #222;
}
</style>
</head>

<body>

<div class="wrapper">

<h1>📊 LCR – Financial Dashboard</h1>

<div class="filter-box">
    <label>Client:</label>
    <select id="client_filter"></select>

    <label>Start date:</label>
    <input type="date" id="start_date">

    <label>End date:</label>
    <input type="date" id="end_date">

    <button onclick="loadData()">Apply</button>
</div>

<div class="card-grid">
    <div class="card"><h3>Weekly Revenue</h3><span id="week_rev">$0.00</span></div>
    <div class="card"><h3>Weekly Material</h3><span id="week_mat">$0.00</span></div>
    <div class="card"><h3>Weekly Profit</h3><span id="week_profit">$0.00</span></div>

    <div class="card"><h3>Monthly Revenue</h3><span id="month_rev">$0.00</span></div>
    <div class="card"><h3>Monthly Material</h3><span id="month_mat">$0.00</span></div>
    <div class="card"><h3>Monthly Profit</h3><span id="month_profit">$0.00</span></div>
</div>

<canvas id="chart1" style="margin-top:30px;"></canvas>
<canvas id="chart2" style="margin-top:30px;"></canvas>

<table>
<thead>
<tr>
<th>#</th>
<th>Date</th>
<th>Client</th>
<th>Address</th>
<th>Total</th>
<th>Material</th>
<th>Labor</th>
</tr>
</thead>
<tbody id="invoice_table"></tbody>
</table>

</div>

<script>
async function loadData(){

    const client = document.getElementById("client_filter").value;
    const start  = document.getElementById("start_date").value;
    const end    = document.getElementById("end_date").value;

    const url = `/LCR/api/dashboard_data.php?client=${client}&start=${start}&end=${end}`;
    const raw = await fetch(url);
    const data = await raw.json();

    fillTable(data);
}

function fillTable(data){
    let html = "";
    data.forEach(r=>{
        html += `
        <tr>
            <td>${r.invoice_number}</td>
            <td>${r.service_date}</td>
            <td>${r.client_internal ?? ""}</td>
            <td>${r.address ?? ""}</td>
            <td>$${r.total}</td>
            <td>$${r.material_cost}</td>
            <td>$${r.labor_cost}</td>
        </tr>
        `;
    });
    document.getElementById("invoice_table").innerHTML = html;
}

loadData();
</script>

</body>
</html>
