<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? null;
$fecha_inicio = date(
    "Y-m-d H:i:s",
    strtotime($data["fecha_inicio"])
);
$turno = $data["turno"];
$encargado = $data["encargado"];
$monto_inicial = floatval($data["monto_inicial"] ?? 0);

if (!$fecha_inicio || !$turno || !$encargado) {
    echo json_encode(["status" => "error", "message" => "Complete todos los campos"]);
    exit;
}

if ($id) {
    $stmt = $db->prepare("UPDATE caja SET fecha_inicio = ?, turno = ?, encargado = ?, monto_inicial = ? WHERE id = ? AND estado = 'ABIERTA'");
    $stmt->execute([$fecha_inicio, $turno, $encargado, $monto_inicial, $id]);
} else {
    $existe = $db->query("SELECT id FROM caja WHERE estado = 'ABIERTA' LIMIT 1")->fetch();
    if ($existe) {
        echo json_encode(["status" => "error", "message" => "Ya hay una caja abierta, ciérrela primero"]);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO caja (fecha_inicio, turno, encargado, monto_inicial) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fecha_inicio, $turno, $encargado, $monto_inicial]);
    $id = $db->lastInsertId();
}

echo json_encode(["status" => "ok", "id" => $id]);
