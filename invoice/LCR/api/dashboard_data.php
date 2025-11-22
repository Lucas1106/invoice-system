
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

declare(strict_types=1);
require "../../auth/db.php"; 

$client = $_GET["client"] ?? "";
$start = $_GET["start"] ?? "2000-01-01";
$end   = $_GET["end"]   ?? "2100-01-01";

$sql = "
SELECT 
    id,
    invoice_number,
    invoice_counter,
    client_internal,
    address,
    service_date,
    total,
    (
        SELECT SUM(JSON_EXTRACT(j.value, '$.cost'))
        FROM JSON_TABLE(items_json, '$[*]' COLUMNS (
            type VARCHAR(20) PATH '$.type',
            cost DECIMAL(10,2) PATH '$.cost'
        )) AS j
        WHERE j.type = 'Material'
    ) AS material_cost,
    (
        SELECT SUM(JSON_EXTRACT(j.value, '$.cost'))
        FROM JSON_TABLE(items_json, '$[*]' COLUMNS (
            type VARCHAR(20) PATH '$.type',
            cost DECIMAL(10,2) PATH '$.cost'
        )) AS j
        WHERE j.type = 'Labor'
    ) AS labor_cost
FROM invoices_lcr
WHERE service_date BETWEEN :start AND :end
";

if ($client !== "") {
    $sql .= " AND client_internal = :client";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":start",$start);
$stmt->bindValue(":end",$end);

if ($client !== "") {
    $stmt->bindValue(":client",$client);
}

$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
