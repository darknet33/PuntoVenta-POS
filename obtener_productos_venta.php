<?php
include("config.php");

$q = $_GET['q'] ?? '';

$stmt = $db->prepare("
SELECT p.id, p.producto, p.precio_detalle AS precio, p.stock, c.nombre AS categoria
FROM productos p
LEFT JOIN categorias c ON c.id = p.categoria_id
WHERE p.producto LIKE :q
LIMIT 10
");

$stmt->execute([
":q" => "%$q%"
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
