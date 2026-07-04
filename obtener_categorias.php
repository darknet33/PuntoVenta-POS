<?php
include("config.php");

$stmt = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
