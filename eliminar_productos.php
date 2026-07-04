<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("DELETE FROM productos WHERE id=:id");
$stmt->execute([":id" => $data["id"]]);

echo json_encode(["status" => "ok"]);
?>