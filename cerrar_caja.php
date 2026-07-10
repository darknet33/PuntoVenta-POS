<?php
include("config.php");

$data = json_decode(file_get_contents("php://input"), true);
$caja_id = intval($data["caja_id"]);

$corte_200 = intval($data["corte_200"] ?? 0);
$corte_100 = intval($data["corte_100"] ?? 0);
$corte_50 = intval($data["corte_50"] ?? 0);
$corte_20 = intval($data["corte_20"] ?? 0);
$corte_10 = intval($data["corte_10"] ?? 0);
$corte_5 = intval($data["corte_5"] ?? 0);
$corte_2 = intval($data["corte_2"] ?? 0);
$corte_1 = intval($data["corte_1"] ?? 0);
$corte_05 = floatval($data["corte_05"] ?? 0);
$corte_02 = floatval($data["corte_02"] ?? 0);
$corte_01 = floatval($data["corte_01"] ?? 0);
$qr_real = floatval($data["qr_real"] ?? 0);

$total_cortes = $corte_200 * 200 + $corte_100 * 100 + $corte_50 * 50 + $corte_20 * 20
    + $corte_10 * 10 + $corte_5 * 5 + $corte_2 * 2 + $corte_1 * 1
    + $corte_05 * 0.5 + $corte_02 * 0.2 + $corte_01 * 0.1;

$stmt = $db->prepare("SELECT * FROM caja WHERE id = ? AND estado = 'ABIERTA'");
$stmt->execute([$caja_id]);
$caja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caja) {
    echo json_encode(["status" => "error", "message" => "Caja no encontrada o ya cerrada"]);
    exit;
}

$hoy = str_replace('T', ' ', $caja['fecha_inicio']);

if (strlen($hoy) == 16) {
    $hoy .= ':00';
}

$stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE fecha >= :hoy AND metodo_pago = 'EFECTIVO'");
$stmt->execute([":hoy" => $hoy]);
$ventas_efectivo = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM equipajes WHERE fecha_creacion >= :hoy AND metodo_pago = 'EFECTIVO'");
$stmt->execute([":hoy" => $hoy]);
$equipaje_efectivo = $stmt->fetchColumn();

$efectivo_esperado = $caja["monto_inicial"] + $ventas_efectivo + $equipaje_efectivo;
$diferencia = round($total_cortes - $efectivo_esperado, 2);
$ahora = date("Y-m-d H:i:s");

$stmt = $db->prepare("UPDATE caja SET
    fecha_cierre = ?, estado = 'CERRADA',
    corte_200 = ?, corte_100 = ?, corte_50 = ?, corte_20 = ?,
    corte_10 = ?, corte_5 = ?, corte_2 = ?, corte_1 = ?,
    corte_05 = ?, corte_02 = ?, corte_01 = ?,
    qr_real = ?, total_cortes = ?, diferencia = ?
    WHERE id = ?");
$stmt->execute([
    $ahora,
    $corte_200, $corte_100, $corte_50, $corte_20,
    $corte_10, $corte_5, $corte_2, $corte_1,
    $corte_05, $corte_02, $corte_01,
    $qr_real, $total_cortes, $diferencia,
    $caja_id
]);

echo json_encode([
    "status" => "ok",
    "diferencia" => $diferencia,
    "total_cortes" => $total_cortes,
    "efectivo_esperado" => $efectivo_esperado
]);
