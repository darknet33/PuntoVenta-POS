<?php
include("config.php");

$stmt = $db->query("SELECT * FROM caja WHERE estado = 'ABIERTA' ORDER BY id DESC LIMIT 1");
$caja = $stmt->fetch(PDO::FETCH_ASSOC);

if ($caja) {
    $hoy = str_replace('T', ' ', $caja['fecha_inicio']);

if (strlen($hoy) == 16) {
    $hoy .= ':00';
}

    $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE fecha >= :hoy AND metodo_pago = 'EFECTIVO'");
    $stmt->execute([":hoy" => $hoy]);
    $ventas_efectivo = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE fecha >= :hoy AND metodo_pago = 'QR'");
    $stmt->execute([":hoy" => $hoy]);
    $ventas_qr = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM equipajes WHERE fecha_creacion >= :hoy AND metodo_pago = 'EFECTIVO'");
    $stmt->execute([":hoy" => $hoy]);
    $equipaje_efectivo = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM equipajes WHERE fecha_creacion >= :hoy AND metodo_pago = 'QR'");
    $stmt->execute([":hoy" => $hoy]);
    $equipaje_qr = $stmt->fetchColumn();

    $total_ventas = $ventas_efectivo + $ventas_qr;
    $total_equipajes = $equipaje_efectivo + $equipaje_qr;

    echo json_encode([
        "caja" => $caja,
        "ventas_efectivo" => $ventas_efectivo,
        "ventas_qr" => $ventas_qr,
        "total_ventas" => $total_ventas,
        "equipaje_efectivo" => $equipaje_efectivo,
        "equipaje_qr" => $equipaje_qr,
        "total_equipajes" => $total_equipajes,
        "efectivo_esperado" => $caja["monto_inicial"] + $ventas_efectivo + $equipaje_efectivo,
        "qr_sistema" => $ventas_qr + $equipaje_qr
    ]);
} else {
    echo json_encode(["caja" => null]);
}
