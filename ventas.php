<?php include("config.php"); ?>

<div class="ventas-layout">

  <div class="ventas-productos">
    <div class="panel">
      <h2>Ventas</h2>
      <div class="search-wrapper">
        <input type="text" id="buscarProducto" placeholder="Buscar producto..." onkeyup="buscarProductos(); toggleClearBtn(this)">
        <button class="search-clear" onclick="limpiarBusqueda('buscarProducto', buscarProductos)">&times;</button>
      </div>
      <div class="toggle-paquete">
        <label class="switch">
          <input type="checkbox" id="togglePaquete" onchange="buscarProductos()">
          <span class="slider"></span>
        </label>
        <span class="toggle-label">Venta por paquete</span>
      </div>
      <div id="resultados" class="productos-grid"></div>
    </div>
  </div>

  <div class="ventas-carrito">
    <div class="panel">
      <h3>Carrito</h3>
      <div id="carrito"></div>

      <div class="total-section">
        <h3>Total</h3>
        <span class="total-valor"><span id="total">0</span> Bs</span>
      </div>

      <input type="text" id="clienteNombre" placeholder="Nombre del cliente (opcional)" style="margin-bottom:12px;">

      <h3 style="font-size:14px;margin:20px 0 12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;">Método de pago</h3>

      <div class="pago-opciones">
        <div class="pago-opcion">
          <input type="radio" name="pago" value="EFECTIVO" id="pagoEfectivo" checked>
          <label for="pagoEfectivo">Efectivo</label>
        </div>
        <div class="pago-opcion">
          <input type="radio" name="pago" value="QR" id="pagoQR">
          <label for="pagoQR">QR</label>
        </div>
      </div>

      <h3 style="font-size:14px;margin:16px 0 12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;">Estado de pago</h3>

      <div class="pago-opciones">
        <div class="pago-opcion">
          <input type="radio" name="estado" value="PAGADO" id="estadoPagado" checked>
          <label for="estadoPagado">Pagado</label>
        </div>
        <div class="pago-opcion">
          <input type="radio" name="estado" value="POR_COBRAR" id="estadoPorCobrar">
          <label for="estadoPorCobrar">Por cobrar</label>
        </div>
      </div>

      <button class="btn btn-secondary w-full" onclick="finalizarVenta()">Finalizar Venta</button>
    </div>
  </div>

</div>
