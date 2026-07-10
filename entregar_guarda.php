<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;

if ($id) {
    $stmt = $db->prepare("UPDATE equipajes SET estado = 'ENTREGADO' WHERE id = ?");
    $ok = $stmt->execute([$id]);
    echo json_encode(["status" => $ok ? "ok" : "error"]);
} else {
    echo json_encode(["status" => "error", "message" => "ID no proporcionado"]);
}
