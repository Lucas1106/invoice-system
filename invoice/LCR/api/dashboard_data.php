<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

declare(strict_types=1);
require "../../auth/db.php";

$client = $_GET["client"] ?? "";
$start  = $_GET["start"] ?? "2000-01-01";
$end    = $_GET["end"]   ?? "2100-01-01";

/* 
   BUSCA SIMPLES — SEM JSON_TABLE, SEM SUBSELECTS
*/
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

/*
    AGORA PROCESSAMOS MATERIAL E LABOR EM PHP
*/
foreach ($rows as &$r) {

    $items = json_decode($r["items_json"] ?? "[]", true);

    $mat = 0;
    $lab = 0;

    if (is_array($items)) {
        foreach ($items as $item) {

            $type = $item["type"] ?? "";
            $cost = floatval($item["cost"] ?? 0);

            if ($type === "Material") {
                $mat += $cost;
            }
            if ($type === "Labor") {
                $lab += $cost;
            }
        }
    }

    $r["material_cost"] = $mat;
    $r["labor_cost"]    = $lab;
}

echo json_encode($rows);
?>
