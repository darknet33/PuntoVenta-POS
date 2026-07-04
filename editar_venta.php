<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data["id"]);
$cliente_nombre = $data["cliente_nombre"] ?? null;
$metodo_pago = $data["metodo_pago"];
$estado = $data["estado"];

if (!$id || !$metodo_pago || !$estado) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$stmt = $db->prepare("UPDATE ventas SET cliente_nombre = ?, metodo_pago = ?, estado = ? WHERE id = ?");
$stmt->execute([$cliente_nombre, $metodo_pago, $estado, $id]);

echo json_encode(["status" => "ok"]);
