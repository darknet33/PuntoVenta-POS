<?php
include("config.php");

$caja_id = isset($_GET["caja_id"]) ? intval($_GET["caja_id"]) : 0;

if (!$caja_id) {
    echo json_encode(["status" => "error", "message" => "caja_id requerido"]);
    exit;
}

$stmt = $db->prepare("SELECT * FROM caja WHERE id = ?");
$stmt->execute([$caja_id]);
$caja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caja) {
    echo json_encode(["status" => "error", "message" => "Caja no encontrada"]);
    exit;
}

$desde = $caja["fecha_inicio"];

$stmt = $db->prepare("SELECT * FROM ventas WHERE fecha >= :desde ORDER BY id ASC");
$stmt->execute([":desde" => $desde]);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($ventas as &$v) {
    $stmt = $db->prepare("SELECT * FROM detalle_ventas WHERE venta_id = ?");
    $stmt->execute([$v["id"]]);
    $v["detalle"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($v);

$stmt = $db->prepare("SELECT * FROM equipajes WHERE fecha_creacion >= :desde ORDER BY id ASC");
$stmt->execute([":desde" => $desde]);
$equipajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_ventas = array_sum(array_column($ventas, "total"));
$total_equipajes = array_sum(array_column($equipajes, "monto"));
$ventas_efectivo = array_sum(array_column(array_filter($ventas, fn($v) => $v["metodo_pago"] === "EFECTIVO"), "total"));
$ventas_qr = array_sum(array_column(array_filter($ventas, fn($v) => $v["metodo_pago"] === "QR"), "total"));
$equipaje_efectivo = array_sum(array_column(array_filter($equipajes, fn($e) => $e["metodo_pago"] === "EFECTIVO"), "monto"));
$equipaje_qr = array_sum(array_column(array_filter($equipajes, fn($e) => $e["metodo_pago"] === "QR"), "monto"));

echo json_encode([
    "caja" => $caja,
    "fecha" => $desde,
    "ventas" => $ventas,
    "equipajes" => $equipajes,
    "total_ventas" => $total_ventas,
    "total_equipajes" => $total_equipajes,
    "ventas_efectivo" => $ventas_efectivo,
    "ventas_qr" => $ventas_qr,
    "equipaje_efectivo" => $equipaje_efectivo,
    "equipaje_qr" => $equipaje_qr,
    "gran_total" => $total_ventas + $total_equipajes
]);
