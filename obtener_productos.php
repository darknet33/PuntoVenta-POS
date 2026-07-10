<?php
include("config.php");

$busqueda = $_GET['busqueda'] ?? '';

$stmt = $db->prepare("
SELECT p.id, p.categoria_id, c.nombre AS categoria, p.producto, p.precio_detalle, p.stock
FROM productos p
LEFT JOIN categorias c ON c.id = p.categoria_id
WHERE p.producto LIKE :busqueda
ORDER BY p.id DESC
");

$stmt->execute([
":busqueda" => "%$busqueda%"
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
