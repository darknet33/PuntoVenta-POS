<?php
include("config.php");

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM ventas WHERE id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM detalle_ventas WHERE venta_id = ?");
$stmt->execute([$id]);
$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["venta" => $venta, "detalle" => $detalle]);
