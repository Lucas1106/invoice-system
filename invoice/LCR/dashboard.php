<?php
declare(strict_types=1);
require "../auth/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LCR – Financial Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Mesma folha de estilo do invoice -->
<link rel="stylesheet" href="/LCR/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/phosphor-icons"></script>

<style>

body {
    margin: 0;
    background: #0d0d0d;
    font-family: 'Inter', sans-serif;
    color: white;
}

/* CONTAINER PRINCIPAL */
.dashboard-wrapper {
    width: 100%;
    max-width: 900px;
    margin: auto;
    padding: 15px;
}

/* CABEÇALHO */
.header-title {
    font-size: 26px;
    font-weight: 600;
    margin-top: 5px;
    padding-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title i {
    font-size: 30px;
    color: #4cc2ff;
}

/* FILTROS */
.filter-box {
    background: #141414;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 18px;
    border-radius: 14px;
    box-shadow: 0 0 10px #000;
}

.filter-box label {
    font-size: 13px;
}

.filter-box select,
.filter-box input {
    width: 100%;
    margin-top: 5px;
    margin-bottom: 10px;
    background: #1f1f1f;
    border: none;
    padding: 10px;
    border-radius: 10px;
    color: white;
}

.apply-btn {
    width: 100%;
    background: #4cc2ff;
    padding: 10px;
    border: none;
    border-radius: 10px;
    color: black;
    font-weight: 600;
    font-size: 15px;
}

/* GRID DE CARDS */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(140px,1fr));
    gap: 12px;
    margin-top: 15px;
}

.stat-card {
    background: rgba(255,255,255,0.06);
    padding: 18px;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
}

.stat-card h3 {
    font-size: 14px;
    font-weight: 600;
    color: #4cc2ff;
    margin-bottom: 5px;
}

.stat-card span {
    font-size: 20px;
    font-weight: 700;
}

/* GRÁFICOS */
.chart-box {
    background: rgba(255,255,255,0.05);
    padding: 20px;
    border-radius: 16px;
    margin-top: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
}

/* TABELA */
table {
    width: 100%;
    background: #111;
    border-collapse: collapse;
    margin-top: 25px;
    border-radius: 12px;
    overflow: hidden;
    font-size: 13px;
}

th {
    background: #1b1b1b;
    padding: 12px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #222;
}

@media(max-width: 600px){
    th:nth-child(4), td:nth-child(4){
        display:none;
    }
}

</style>
</head>

<body>

<div class="dashboard-wrapper">

    <div class="header-title">
        <i class="ph-chart-line-up"></i> LCR – Financial Dashboard
    </div>

    <!-- FILTROS -->
    <div class="filter-box">
        <label>Client:</label>
        <select id="client_filter"></select>

        <label>Start date:</label>
        <input type="date" id="start_date">

        <label>End date:</label>
        <input type="date" id="end_date">

        <button class="apply-btn" onclick="loadData()">Apply</button>
    </div>

    <!-- CARDS -->
    <div class="card-grid">
        <div class="stat-card"><h3>Weekly Revenue</h3><span id="week_rev">$0.00</span></div>
        <div class="stat-card"><h3>Weekly Material</h3><span id="week_mat">$0.00</span></div>
        <div class="stat-card"><h3>Weekly Profit</h3><span id="week_profit">$0.00</span></div>

        <div class="stat-card"><h3>Monthly Revenue</h3><span id="month_rev">$0.00</span></div>
        <div class="stat-card"><h3>Monthly Material</h3><span id="month_mat">$0.00</span></div>
        <div class="stat-card"><h3>Monthly Profit</h3><span id="month_profit">$0.00</span></div>
    </div>

    <!-- GRÁFICO -->
    <div class="chart-box">
        <canvas id="monthChart"></canvas>
    </div>

    <div class="chart-box">
        <canvas id="typeChart"></canvas>
    </div>

    <!-- TABELA -->
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
let monthChart, typeChart;

async function loadData() {
    const client = document.getElementById("client_filter").value;
    const start = document.getElementById("start_date").value;
    const end   = document.getElementById("end_date").value;

    const url = `/LCR/api/dashboard_data.php?client=${client}&start=${start}&end=${end}`;
    const data = await (await fetch(url)).json();

    fillTable(data);
    calculateTotals(data);
    renderCharts(data);
}

function fillTable(data) {
    let html = "";
    data.forEach(row => {
        html += `
        <tr>
            <td>${row.invoice_number}</td>
            <td>${row.service_date}</td>
            <td>${row.client_internal ?? ''}</td>
            <td>${row.address ?? ''}</td>
            <td>$${row.total}</td>
            <td>$${row.material_cost}</td>
            <td>$${row.labor_cost}</td>
        </tr>`;
    });
    document.getElementById("invoice_table").innerHTML = html;
}

function calculateTotals(data) {
    const today = new Date();
    const week = getWeekNumber(today);
    const month = today.getMonth() + 1;

    let weekRev=0, weekMat=0;
    let monRev=0, monMat=0;

    data.forEach(row => {
        const d = new Date(row.service_date);
        const rowWeek = getWeekNumber(d);
        const rowMonth = d.getMonth() + 1;

        if (rowWeek === week) {
            weekRev += parseFloat(row.total);
            weekMat += parseFloat(row.material_cost);
        }
        if (rowMonth === month) {
            monRev += parseFloat(row.total);
            monMat += parseFloat(row.material_cost);
        }
    });

    document.getElementById("week_rev").innerText = "$"+weekRev.toFixed(2);
    document.getElementById("week_mat").innerText = "$"+weekMat.toFixed(2);
    document.getElementById("week_profit").innerText = "$"+(weekRev-weekMat).toFixed(2);

    document.getElementById("month_rev").innerText = "$"+monRev.toFixed(2);
    document.getElementById("month_mat").innerText = "$"+monMat.toFixed(2);
    document.getElementById("month_profit").innerText = "$"+(monRev-monMat).toFixed(2);
}

function getWeekNumber(d) {
    const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const day = date.getUTCDay() || 7;
    date.setUTCDate(date.getUTCDate() + 4 - day);
    const yearStart = new Date(Date.UTC(date.getUTCFullYear(),0,1));
    return Math.ceil(((date - yearStart) / 86400000 + 1)/7);
}

function renderCharts(data) {
    const monthTotals = {};
    let matTotal=0, labTotal=0;

    data.forEach(row => {
        const m = row.service_date.substring(0,7);
        monthTotals[m] = (monthTotals[m] || 0) + parseFloat(row.total);
        matTotal += parseFloat(row.material_cost);
        labTotal += parseFloat(row.labor_cost);
    });

    const labels = Object.keys(monthTotals);
    const values = Object.values(monthTotals);

    if (monthChart) monthChart.destroy();
    if (typeChart) typeChart.destroy();

    monthChart = new Chart(document.getElementById("monthChart"), {
        type: "bar",
        data: {
            labels,
            datasets: [{ label: "Revenue", data: values, backgroundColor:"#4cc2ff" }]
        }
    });

    typeChart = new Chart(document.getElementById("typeChart"), {
        type: "pie",
        data: {
            labels:["Material","Labor"],
            datasets:[{ data:[matTotal, labTotal], backgroundColor:["#ff6666","#66ff99"] }]
        }
    });
}

loadData();
</script>

</body>
</html>
