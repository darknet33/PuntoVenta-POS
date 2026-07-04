<?php include("config.php"); ?>

<div class="panel">
  <div class="historial-header">
    <h2 style="margin:0;">Historial de Ventas</h2>
    <button class="btn btn-primary" onclick="cargarHistorial()">Actualizar</button>
  </div>

  <div id="resumen"></div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Cliente</th>
          <th>Método</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody id="tablaHistorial"></tbody>
    </table>
  </div>
</div>

<div id="detalleVenta"></div>

<div id="modalEditarVenta" class="modal">
  <div class="modal-content" style="max-width:380px;">
    <h3>Editar Venta #<span id="editVentaId"></span></h3>
    <input type="hidden" id="editId">
    <input type="text" id="editCliente" placeholder="Nombre del cliente" style="margin-bottom:12px;">
    <label style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.04em;">Método de Pago</label>
    <div class="pago-opciones" style="margin:8px 0 16px;">
      <div class="pago-opcion">
        <input type="radio" name="editPago" value="EFECTIVO" id="editPagoEfectivo">
        <label for="editPagoEfectivo">Efectivo</label>
      </div>
      <div class="pago-opcion">
        <input type="radio" name="editPago" value="QR" id="editPagoQR">
        <label for="editPagoQR">QR</label>
      </div>
    </div>
    <label style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.04em;">Estado</label>
    <div class="pago-opciones" style="margin:8px 0 16px;">
      <div class="pago-opcion">
        <input type="radio" name="editEstado" value="PAGADO" id="editEstadoPagado">
        <label for="editEstadoPagado">Pagado</label>
      </div>
      <div class="pago-opcion">
        <input type="radio" name="editEstado" value="POR_COBRAR" id="editEstadoPorCobrar">
        <label for="editEstadoPorCobrar">Por cobrar</label>
      </div>
    </div>
    <div class="modal-buttons">
      <button class="btn btn-primary" onclick="guardarEditarVenta()">Guardar</button>
      <button class="btn btn-ghost" onclick="cerrarEditarVenta()">Cancelar</button>
    </div>
  </div>
</div>
