<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);

$db->beginTransaction();

try {
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("
        INSERT INTO ventas (fecha, metodo_pago, total, cliente_nombre, estado)
        VALUES (:fecha, :metodo_pago, :total, :cliente_nombre, :estado)
    ");
    $stmt->execute([
        ":fecha"          => $ahora,
        ":metodo_pago"    => $data["metodo_pago"],
        ":total"          => $data["total"],
        ":cliente_nombre" => $data["cliente_nombre"] ?? null,
        ":estado"         => $data["estado"] ?? "PAGADO"
    ]);
    $venta_id = $db->lastInsertId();

    $stmt = $db->prepare("
        INSERT INTO detalle_ventas (venta_id, producto_id, producto, precio, cantidad, subtotal)
        VALUES (:venta_id, :producto_id, :producto, :precio, :cantidad, :subtotal)
    ");

    foreach ($data["carrito"] as $item) {
        $descuento = isset($item["descuento"]) ? floatval($item["descuento"]) : 0;
        $precio_final = floatval($item["precio"]) - $descuento;
        $subtotal = $precio_final * $item["cantidad"];
        $stmt->execute([
            ":venta_id"    => $venta_id,
            ":producto_id" => $item["id"],
            ":producto"    => $item["producto"],
            ":precio"      => $precio_final,
            ":cantidad"    => $item["cantidad"],
            ":subtotal"    => $subtotal
        ]);
    }

    $db->commit();
    echo json_encode(["status" => "ok"]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
