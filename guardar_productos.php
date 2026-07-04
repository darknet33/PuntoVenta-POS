<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("
INSERT INTO productos (categoria_id, producto, precio_detalle)
VALUES (:categoria_id, :producto, :precio)
");

$stmt->execute([
":categoria_id" => $data["categoria_id"],
":producto" => $data["producto"],
":precio" => $data["precio"]
]);

echo json_encode(["status" => "ok"]);
