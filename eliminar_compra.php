<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$db->beginTransaction();

try {
    $compra_id = $data["id"];

    $stmt = $db->prepare("SELECT producto_id, cantidad FROM detalle_compras WHERE compra_id = :compra_id");
    $stmt->execute([":compra_id" => $compra_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updStock = $db->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");
    foreach ($items as $item) {
        $updStock->execute([":cantidad" => $item["cantidad"], ":id" => $item["producto_id"]]);
    }

    $del = $db->prepare("DELETE FROM detalle_compras WHERE compra_id = :compra_id");
    $del->execute([":compra_id" => $compra_id]);

    $del2 = $db->prepare("DELETE FROM compras WHERE id = :id");
    $del2->execute([":id" => $compra_id]);

    $db->commit();
    echo json_encode(["status" => "ok"]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
