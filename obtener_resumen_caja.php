<?php
include("config.php");

$monto_inicial = isset($_GET["monto_inicial"]) ? floatval($_GET["monto_inicial"]) : 0;
$hoy = date("Y-m-d");

$stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha) = :hoy");
$stmt->execute([":hoy" => $hoy]);
$total = $stmt->fetch(PDO::FETCH_ASSOC)["total"];

$stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha) = :hoy AND metodo_pago = 'EFECTIVO'");
$stmt->execute([":hoy" => $hoy]);
$efectivo = $stmt->fetch(PDO::FETCH_ASSOC)["total"];

$stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha) = :hoy AND metodo_pago = 'QR'");
$stmt->execute([":hoy" => $hoy]);
$qr = $stmt->fetch(PDO::FETCH_ASSOC)["total"];

$stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM equipajes WHERE DATE(fecha_creacion) = :hoy AND metodo_pago = 'EFECTIVO'");
$stmt->execute([":hoy" => $hoy]);
$efectivo_equipaje = $stmt->fetch(PDO::FETCH_ASSOC)["total"];

$stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM equipajes WHERE DATE(fecha_creacion) = :hoy AND metodo_pago = 'QR'");
$stmt->execute([":hoy" => $hoy]);
$qr_equipaje = $stmt->fetch(PDO::FETCH_ASSOC)["total"];

$total_general = $total + $efectivo_equipaje + $qr_equipaje;
$efectivo_total = $efectivo + $efectivo_equipaje;
$qr_total = $qr + $qr_equipaje;

echo json_encode([
    "total" => $total_general,
    "efectivo" => $efectivo,
    "qr" => $qr,
    "efectivo_equipaje" => $efectivo_equipaje,
    "qr_equipaje" => $qr_equipaje,
    "total_equipaje" => $efectivo_equipaje + $qr_equipaje,
    "monto_inicial" => $monto_inicial,
    "efectivo_esperado" => $monto_inicial + $efectivo_total
]);
