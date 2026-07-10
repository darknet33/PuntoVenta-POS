<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$db->beginTransaction();

try {
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("
        INSERT INTO compras (fecha, metodo_pago, total)
        VALUES (:fecha, :metodo_pago, :total)
    ");
    $stmt->execute([
        ":fecha"       => $ahora,
        ":metodo_pago" => $data["metodo_pago"],
        ":total"       => $data["total"]
    ]);
    $compra_id = $db->lastInsertId();

    $stmt = $db->prepare("
        INSERT INTO detalle_compras (compra_id, producto_id, producto, cantidad, costo, subtotal)
        VALUES (:compra_id, :producto_id, :producto, :cantidad, :costo, :subtotal)
    ");
    $updStock = $db->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id");

    foreach ($data["carrito"] as $item) {
        $subtotal = floatval($item["costo"]) * intval($item["cantidad"]);
        $stmt->execute([
            ":compra_id"  => $compra_id,
            ":producto_id" => $item["id"],
            ":producto"   => $item["producto"],
            ":cantidad"   => $item["cantidad"],
            ":costo"      => $item["costo"],
            ":subtotal"   => $subtotal
        ]);
        $updStock->execute([":cantidad" => $item["cantidad"], ":id" => $item["id"]]);
    }

    $db->commit();
    echo json_encode(["status" => "ok"]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
