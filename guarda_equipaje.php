<?php include("config.php"); ?>

<div class="ventas-layout">

  <div class="ventas-productos">
    <div class="panel">
      <h2>Registrar Guarda Equipaje</h2>
      <form id="formGuarda" onsubmit="event.preventDefault(); guardarGuarda();">
        <input type="text" id="gNombre" placeholder="Nombre completo" required style="margin-bottom:12px;">
        <input type="text" id="gCedula" placeholder="Cédula de Identidad" required style="margin-bottom:12px;">
        <textarea id="gEquipaje" rows="3" placeholder="Detalle del equipaje (artículos, cantidad, etc.)" required style="width:100%;padding:12px 16px;font-family:'Inter',sans-serif;font-size:14px;border:1px solid rgba(0,0,0,0.08);border-radius:var(--radius-md);background:rgba(255,255,255,0.5);color:var(--text-primary);outline:none;resize:vertical;min-height:80px;margin-bottom:12px;"></textarea>
        <input type="datetime-local" id="gFechaRecojo" required style="margin-bottom:12px;">
        <input type="number" id="gMonto" placeholder="Monto (Bs)" step="0.01" required style="margin-bottom:12px;">

        <h3 style="font-size:14px;margin:16px 0 12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.05em;">Método de pago</h3>
        <div class="pago-opciones" style="margin:0 0 16px;">
          <div class="pago-opcion">
            <input type="radio" name="gPago" value="EFECTIVO" id="gPagoEfectivo" checked>
            <label for="gPagoEfectivo">Efectivo</label>
          </div>
          <div class="pago-opcion">
            <input type="radio" name="gPago" value="QR" id="gPagoQR">
            <label for="gPagoQR">QR</label>
          </div>
        </div>

        <button type="submit" class="btn btn-secondary w-full">Registrar Guarda Equipaje</button>
      </form>
    </div>
  </div>

  <div class="ventas-carrito">
    <div class="panel">
      <div class="flex items-center justify-between gap-4" style="flex-wrap:wrap;margin-bottom:16px;">
        <h2 style="margin:0;">Historial</h2>
        <div style="display:flex;align-items:center;gap:8px;">
          <select id="filtroEstado" onchange="cargarGuardas()" style="padding:6px 12px;border-radius:var(--radius-sm);border:1px solid rgba(0,0,0,0.08);background:rgba(255,255,255,0.5);color:var(--text-primary);outline:none;font-family:'Inter',sans-serif;font-size:13px;">
            <option value="TODOS">Todos</option>
            <option value="PENDIENTE">Pendientes</option>
            <option value="ENTREGADO">Entregados</option>
          </select>
        </div>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>CI</th>
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

</div>
