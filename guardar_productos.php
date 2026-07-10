<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("
INSERT INTO productos (categoria_id, producto, precio_detalle, stock, unidades_por_paquete)
VALUES (:categoria_id, :producto, :precio, :stock, :unidades_por_paquete)
");

$stmt->execute([
    ":categoria_id" => $data["categoria_id"],
    ":producto" => $data["producto"],
    ":precio" => $data["precio"],
    ":stock" => $data["stock"] ?? 0,
    ":unidades_por_paquete" => $data["unidades_por_paquete"] ?? 1
]);

echo json_encode(["status" => "ok"]);
