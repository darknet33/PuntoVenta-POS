<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("
UPDATE productos
SET categoria_id=:categoria_id,
    producto=:producto,
    precio_detalle=:precio
WHERE id=:id
");

$stmt->execute([
":id" => $data["id"],
":categoria_id" => $data["categoria_id"],
":producto" => $data["producto"],
":precio" => $data["precio"]
]);

echo json_encode(["status" => "ok"]);
