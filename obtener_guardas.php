<?php
include("config.php");

if (isset($_GET["id"])) {
    $stmt = $db->prepare("SELECT * FROM equipajes WHERE id = ?");
    $stmt->execute([$_GET["id"]]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} else {
    $stmt = $db->query("SELECT * FROM equipajes ORDER BY id DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
