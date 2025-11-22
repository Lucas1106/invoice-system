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
    MariaDB FIX – NÃO usar JSON_TABLE
    Vamos extrair Material e Labor manualmente
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

    /* MATERIAL */
    (
        SELECT COALESCE(SUM(JSON_EXTRACT(j.value, '$.cost')),0)
        FROM JSON_TABLE_MARIADB(items_json) AS j
        WHERE JSON_EXTRACT(j.value, '$.type') = '\"Material\"'
    ) AS material_cost,

    /* LABOR */
    (
        SELECT COALESCE(SUM(JSON_EXTRACT(j.value, '$.cost')),0)
        FROM JSON_TABLE_MARIADB(items_json) AS j
        WHERE JSON_EXTRACT(j.value, '$.type') = '\"Labor\"'
    ) AS labor_cost

FROM invoices_lcr
WHERE service_date BETWEEN :start AND :end
";

/*
    CRIAÇÃO DE UMA FUNÇÃO VIRTUAL PARA SIMULAR JSON_TABLE
    (funciona em MariaDB)
*/

$sql = str_replace(
    "JSON_TABLE_MARIADB(items_json)",
    "(SELECT JSON_EXTRACT(items_json, CONCAT('$[', numbers.n, ']')) AS value
      FROM (
            SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION
            SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION
            SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION
