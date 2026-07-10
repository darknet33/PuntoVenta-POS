<?php include("config.php"); ?>

<div class="ventas-layout">

  <div class="ventas-productos">
    <div class="panel">
      <h2>Compras</h2>
      <div class="search-wrapper">
        <input type="text" id="buscarProductoCompra" placeholder="Buscar producto..." onkeyup="buscarProductosCompra(); toggleClearBtn(this)">
        <button class="search-clear" onclick="limpiarBusqueda('buscarProductoCompra', buscarProductosCompra)">&times;</button>
      </div>
      <div id="resultadosCompra" class="productos-grid"></div>
    </div>
  </div>

  <div class="ventas-carrito">
    <div class="panel">
      <h3>Carrito de Compras</h3>
      <div id="carritoCompra"></div>

      <div class="total-section">
        <h3>Total</h3>
        <span class="total-valor"><span id="totalCompra">0</span> Bs</span>
      </div>

      <h3 style="font-size:14px;margin:20px 0 12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;">Método de pago</h3>

      <div class="pago-opciones">
        <div class="pago-opcion">
          <input type="radio" name="pagoCompra" value="EFECTIVO" id="pagoCompraEfectivo" checked>
          <label for="pagoCompraEfectivo">Efectivo</label>
        </div>
        <div class="pago-opcion">
          <input type="radio" name="pagoCompra" value="QR" id="pagoCompraQR">
          <label for="pagoCompraQR">QR</label>
        </div>
      </div>

      <button class="btn btn-secondary w-full" onclick="finalizarCompra()">Registrar Compra</button>
    </div>
  </div>

</div>

<div class="panel" style="margin-top:20px;">
  <h2>Historial de Compras</h2>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Productos</th>
          <th>Total</th>
          <th>Pago</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaCompras"></tbody>
    </table>
  </div>
</div>
