<?php
declare(strict_types=1);
require "../auth/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LCR – Financial Dashboard</title>
<link rel="stylesheet" href="/invoice/LCR/style.css"> <!-- usa o mesmo layout -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Mesmo estilo da página de invoice */
.dashboard-container {
    color: white;
    padding: 25px;
}

.card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 30px;
}

.card {
    background: #1c1c1c;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0px 0px 8px #000;
}

.card h3 {
    margin-bottom: 6px;
    color: #4cc2ff;
}

.filter-area {
    margin-bottom: 25px;
    background: #151515;
    padding: 15px;
    border-radius: 12px;
}

.filter-area select, .filter-area input {
    background: #222;
    color: white;
    padding: 8px;
    border-radius: 6px;
    border: none;
    margin-right: 12px;
}

.table-area {
    margin-top: 30px;
}

table {
    width: 100%;
    background: #121212;
    border-collapse: collapse;
    color: white;
}

table th, table td {
    padding: 12px;
    border-bottom: 1px solid #333;
}

</style>
</head>

<body>

<div class="dashboard-container">

    <h1 style="margin-bottom:20px;">📊 LCR – Financial Dashboard</h1>

    <!-- FILTROS -->
    <div class="filter-area">
        <label>Client:</label>
        <select id="client_filter"></select>

        <label>Start:</label>
        <input type="date" id="start_date">

        <label>End:</label>
        <input type="date" id="end_date">

        <button onclick="loadData()" 
            style="padding:8px 18px; background:#4cc2ff; color:black; border:none; border-radius:6px;">
            Apply
        </button>
    </div>

    <!-- RESUMO -->
    <div class="card-grid">
        <div class="card"><h3>Weekly Revenue</h3><span id="week_rev">$0.00</span></div>
        <div class="card"><h3>Weekly Material</h3><span id="week_mat">$0.00</span></div>
        <div class="card"><h3>Weekly Profit</h3><span id="week_profit">$0.00</span></div>

        <div class="card"><h3>Monthly Revenue</h3><span id="month_rev">$0.00</span></div>
        <div class="card"><h3>Monthly Material</h3><span id="month_mat">$0.00</span></div>
        <div class="card"><h3>Monthly Profit</h3><span id="month_profit">$0.00</span></div>
    </div>

    <!-- GRÁFICOS -->
    <div style="display:flex; gap:20px; margin-top:25px;">
        <div style="width:50%;"><canvas id="monthChart"></canvas></div>
        <div style="width:50%;"><canvas id="typeChart"></canvas></div>
    </div>

    <!-- TABELA -->
    <div class="table-area">
        <h2>Invoices List</h2>
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

</div>

<script>
let monthChart, typeChart;

async function loadData() {
    const client = document.getElementById("client_filter").value;
    const start = document.getElementById("start_date").value;
    const end   = document.getElementById("end_date").value;

    const url = `/invoice/LCR/api/dashboard_data.php?client=${client}&start=${start}&end=${end}`;

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

    let weekRev=0, weekMat=0, weekLab=0;
    let monRev=0, monMat=0, monLab=0;

    data.forEach(row => {
        const d = new Date(row.service_date);
        const rowWeek = getWeekNumber(d);
        const rowMonth = d.getMonth() + 1;

        if (rowWeek === week) {
            weekRev += parseFloat(row.total);
            weekMat += parseFloat(row.material_cost);
            weekLab += parseFloat(row.labor_cost);
        }
        if (rowMonth === month) {
            monRev += parseFloat(row.total);
            monMat += parseFloat(row.material_cost);
            monLab += parseFloat(row.labor_cost);
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
        data: { labels, datasets: [{ label: "Revenue", data: values, backgroundColor:"#4cc2ff" }] }
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
