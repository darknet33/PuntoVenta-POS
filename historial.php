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
          <th>Ver</th>
        </tr>
      </thead>
      <tbody id="tablaHistorial"></tbody>
    </table>
  </div>
</div>

<div id="detalleVenta"></div>
