<?php include("config.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Punto de Venta</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app">

  <div id="contenido"></div>

  <nav class="tabbar" id="tabbar">
    <button class="tab" id="btnProductos" data-view="productos">
      <span class="tab-icon">📦</span>
      <span class="tab-label">Productos</span>
    </button>
    <button class="tab" id="btnCompras" data-view="compras">
      <span class="tab-icon">📥</span>
      <span class="tab-label">Compras</span>
    </button>
    <button class="tab" id="btnHistorial" data-view="historial">
      <span class="tab-icon">📋</span>
      <span class="tab-label">Historial</span>
    </button>
    <button class="tab tab-center active" id="btnVentas" data-view="ventas">
      <span class="tab-icon">🛒</span>
      <span class="tab-label">Ventas</span>
    </button>
    <button class="tab" id="btnGuardaEquipaje" data-view="guarda_equipaje">
      <span class="tab-icon">🧳</span>
      <span class="tab-label">Guarda Eq.</span>
    </button>
    <button class="tab" id="btnCierre" data-view="cierre_caja">
      <span class="tab-icon">💰</span>
      <span class="tab-label">Caja</span>
    </button>
  </nav>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="js/app.js"></script>
<script src="js/caja.js"></script>
</body>
</html>
