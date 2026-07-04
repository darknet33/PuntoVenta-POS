<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $db->prepare("DELETE FROM equipajes WHERE id = ?");
$ok = $stmt->execute([$data["id"]]);

echo json_encode(["status" => $ok ? "ok" : "error"]);
