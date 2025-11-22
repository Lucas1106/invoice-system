<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

declare(strict_types=1);

// caminho correto conforme você confirmou:
require /auth/db.php";

$client = $_GET["client"] ?? "";
$start  = $_GET["start"] ?? "2000-01-01";
$end    = $_GET["end"]   ?? "2100-01-01";

$sql = "
SELECT
    id,
    invoice_number,
    invoice_counter,
    client_internal,
    address,
    service_date,
    total,
    items_json
FROM invoices_lcr
WHERE service_date BETWEEN :start AND :end
";

if ($client !== "") {
    $sql .= " AND client_internal = :client";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":start", $start);
$stmt->bindValue(":end", $end);

if ($client !== "") {
    $stmt->bindValue(":client", $client);
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PROCESSAR MATERIAL E LABOR
foreach ($rows as &$row) {

    $items = json_decode($row["items_json"] ?? "[]", true);

    $material = 0;
    $labor    = 0;

    if (is_array($items)) {
        foreach ($items as $i) {

            $type = $i["type"] ?? "";
            $cost = floatval($i["cost"] ?? 0);

            if ($type === "Material") $material += $cost;
            if ($type === "Labor")    $labor    += $cost;
        }
    }

    $row["material_cost"] = $material;
    $row["labor_cost"]    = $labor;
}

echo json_encode($rows);
