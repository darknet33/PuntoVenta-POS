<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$ahora = date("Y-m-d H:i:s");

$stmt = $db->prepare("INSERT INTO equipajes (nombre_completo, cedula_identidad, equipaje, fecha_recojo, monto, metodo_pago, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
$ok = $stmt->execute([
    $data["nombre_completo"],
    $data["cedula_identidad"],
    $data["equipaje"],
    $data["fecha_recojo"],
    $data["monto"],
    $data["metodo_pago"],
    $ahora
]);

echo json_encode(["status" => $ok ? "ok" : "error"]);
