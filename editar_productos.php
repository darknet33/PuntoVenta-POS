<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("
UPDATE productos
SET categoria_id=:categoria_id,
    producto=:producto,
    precio_detalle=:precio,
    stock=:stock,
    unidades_por_paquete=:unidades_por_paquete
WHERE id=:id
");

$stmt->execute([
    ":id" => $data["id"],
    ":categoria_id" => $data["categoria_id"],
    ":producto" => $data["producto"],
    ":precio" => $data["precio"],
    ":stock" => $data["stock"] ?? 0,
    ":unidades_por_paquete" => $data["unidades_por_paquete"] ?? 1
]);

echo json_encode(["status" => "ok"]);
