<?php include("config.php"); ?>

<div class="guarda-layout">

  <div class="panel guarda-form-panel">
    <h2>Registrar Guarda Equipaje</h2>
    <form id="formGuarda" onsubmit="event.preventDefault(); guardarGuarda();">
      <div class="guarda-form-grid">
        <div class="guarda-field">
          <label>Nombre Completo</label>
          <input type="text" id="gNombre" placeholder="Nombre completo" required>
        </div>
        <div class="guarda-field">
          <label>Cédula de Identidad</label>
          <input type="text" id="gCedula" placeholder="Cédula de identidad" required>
        </div>
        <div class="guarda-field guarda-field-full">
          <label>Equipaje</label>
          <textarea id="gEquipaje" rows="3" placeholder="Detalle del equipaje (artículos, cantidad, etc.)" required></textarea>
        </div>
        <div class="guarda-field">
          <label>Fecha de Recojo</label>
          <input type="datetime-local" id="gFechaRecojo" required>
        </div>
        <div class="guarda-field">
          <label>Monto (Bs)</label>
          <input type="number" id="gMonto" placeholder="0.00" step="0.01" required>
        </div>
        <div class="guarda-field guarda-field-full">
          <label>Método de Pago</label>
          <div class="pago-opciones" style="margin:0;">
            <div class="pago-opcion">
              <input type="radio" name="gPago" value="EFECTIVO" id="gPagoEfectivo" checked>
              <label for="gPagoEfectivo">Efectivo</label>
            </div>
            <div class="pago-opcion">
              <input type="radio" name="gPago" value="QR" id="gPagoQR">
              <label for="gPagoQR">QR</label>
            </div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-secondary w-full" style="margin-top:12px;">Registrar Guarda Equipaje</button>
    </form>
  </div>

  <div class="panel">
    <div class="guarda-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
      <h2 style="margin:0;">Historial</h2>
      <div style="display:flex; align-items:center; gap:8px;">
        <select id="filtroEstado" onchange="cargarGuardas()" style="padding:6px 12px; border-radius:var(--radius-sm); border:1px solid var(--surface-border); background:var(--surface); color:var(--text); outline:none; font-family:inherit;">
          <option value="TODOS">Todos</option>
          <option value="PENDIENTE">Pendientes</option>
          <option value="ENTREGADO">Entregados</option>
        </select>
        <button class="btn btn-primary" onclick="cargarGuardas()">Actualizar</button>
      </div>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Equipaje</th>
            <th>Recojo</th>
            <th>Monto</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="tablaGuardas"></tbody>
      </table>
    </div>
    <div id="detalleGuarda" style="margin-top:16px;"></div>
  </div>

</div>
