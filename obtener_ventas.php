<?php
include("config.php");

$stmt = $db->query("SELECT * FROM ventas ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
