<?php include("config.php"); ?>

<div class="panel">
  <div class="flex items-center justify-between gap-4" style="flex-wrap:wrap;margin-bottom:20px;">
    <h2 style="margin:0;">Productos</h2>
    <button class="btn btn-primary" onclick="mostrarFormulario()">+ Agregar Producto</button>
  </div>

  <div class="search-wrapper">
    <input type="text" id="buscador" placeholder="Buscar producto..." onkeyup="cargarProductos(); toggleClearBtn(this)">
    <button class="search-clear" onclick="limpiarBusqueda('buscador', cargarProductos)">&times;</button>
  </div>

  <div class="table-container" style="margin-top:16px;">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Categoría</th>
          <th>Producto</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaProductos"></tbody>
    </table>
  </div>
</div>

<div id="modal" class="modal">
  <div class="modal-content">
    <h3>Producto</h3>
    <input type="hidden" id="id">
    <input type="hidden" id="categoria_id">
    <select id="categoria" onfocus="cargarCategorias()">
      <option value="">Seleccionar categoría...</option>
    </select>
    <input type="text" id="producto" placeholder="Producto">
    <input type="number" id="precio" placeholder="Precio" step="0.01">
    <input type="number" id="stock" placeholder="Stock" min="0" value="0">
    <div class="modal-buttons">
      <button class="btn btn-primary" onclick="guardarProducto()">Guardar</button>
      <button class="btn btn-ghost" onclick="cerrarModal()">Cancelar</button>
    </div>
  </div>
</div>
