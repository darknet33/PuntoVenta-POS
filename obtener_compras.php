<?php
include("config.php");

$stmt = $db->query("
    SELECT c.id, c.fecha, c.metodo_pago, c.total,
           GROUP_CONCAT(dc.producto || ' x' || dc.cantidad) AS productos
    FROM compras c
    LEFT JOIN detalle_compras dc ON dc.compra_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
