let modo = "nuevo";
let carrito = [];

// ===================== TOAST =====================
function createToastContainer() {
  let c = document.getElementById("toast-container");
  if (!c) {
    c = document.createElement("div");
    c.id = "toast-container";
    c.className = "toast-container";
    document.body.appendChild(c);
  }
  return c;
}

function notify(mensaje, tipo) {
  tipo = tipo || "success";
  let iconos = { success: "✓", error: "✗", info: "ℹ" };
  let container = createToastContainer();
  let t = document.createElement("div");
  t.className = "toast " + tipo;
  t.innerHTML = '<span class="toast-icon">' + (iconos[tipo] || "ℹ") + "</span>" + mensaje;
  container.appendChild(t);
  setTimeout(() => {
    t.classList.add("removing");
    setTimeout(() => t.remove(), 300);
  }, 3000);
}

function toggleClearBtn(input) {
  let wrapper = input.closest(".search-wrapper");
  if (!wrapper) return;
  wrapper.classList.toggle("has-text", input.value.length > 0);
}

function limpiarBusqueda(id, callback) {
  let input = document.getElementById(id);
  if (!input) return;
  input.value = "";
  toggleClearBtn(input);
  if (callback) callback();
}

function setActiveTab(id) {
  document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
  document.getElementById(id)?.classList.add("active");
}

function cargarVista(vista) {
  fetch(vista + ".php")
    .then(res => res.text())
    .then(data => {
      document.getElementById("contenido").innerHTML = data;
      if (vista === "productos") cargarProductos();
      if (vista === "historial") cargarHistorial();
      if (vista === "cierre_caja") cargarCierre();
      if (vista === "guarda_equipaje") cargarGuardas();
    });
}

document.querySelectorAll(".tab").forEach(btn => {
  btn.addEventListener("click", function() {
    let view = this.dataset.view;
    setActiveTab(this.id);
    cargarVista(view);
  });
});

setTimeout(() => cargarVista("ventas"), 50);

// ===================== PRODUCTOS =====================
function cargarCategorias() {
  let sel = document.getElementById("categoria");
  if (sel.dataset.cargado) return;
  fetch("obtener_categorias.php")
    .then(r => r.json())
    .then(data => {
      sel.innerHTML = '<option value="">Seleccionar categoría...</option>';
      data.forEach(c => {
        sel.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
      });
      sel.dataset.cargado = "1";
    });
}

function cargarProductos() {
  let busqueda = document.getElementById("buscador")?.value || "";
  fetch("obtener_productos.php?busqueda=" + busqueda)
    .then(res => res.json())
    .then(data => {
      let html = "";
      data.forEach(p => {
        html += `<tr>
          <td>${p.id}</td>
          <td>${p.categoria}</td>
          <td>${p.producto}</td>
          <td>${p.precio_detalle}</td>
          <td>
            <button class="btn btn-sm btn-primary"
              onclick="editarProducto(this)"
              data-id="${p.id}"
              data-categoria-id="${p.categoria_id}"
              data-producto="${p.producto}"
              data-precio="${p.precio_detalle}">Editar</button>
            <button class="btn btn-sm btn-danger" onclick="eliminar(${p.id})">Eliminar</button>
          </td>
        </tr>`;
      });
      document.getElementById("tablaProductos").innerHTML = html;
    });
}

function mostrarFormulario() {
  cargarCategorias();
  document.getElementById("modal").classList.add("show");
}

function cerrarModal() {
  document.getElementById("modal").classList.remove("show");
  limpiar();
}

function limpiar() {
  document.getElementById("id").value = "";
  document.getElementById("categoria_id").value = "";
  let sel = document.getElementById("categoria");
  sel.value = "";
  sel.dataset.cargado = "";
  document.getElementById("producto").value = "";
  document.getElementById("precio").value = "";
  modo = "nuevo";
}

function guardarProducto() {
  let catId = document.getElementById("categoria").value;
  if (!catId) { notify("Seleccione una categoría", "error"); return; }
  let data = {
    id: document.getElementById("id").value,
    categoria_id: catId,
    producto: document.getElementById("producto").value,
    precio: document.getElementById("precio").value
  };
  let url = modo === "nuevo" ? "guardar_productos.php" : "editar_productos.php";
  let label = modo === "nuevo" ? "guardado" : "actualizado";
  fetch(url, {
    method: "POST",
    body: JSON.stringify(data)
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Producto " + label + " exitosamente", "success");
        cerrarModal();
        cargarProductos();
      } else {
        notify("Error al " + label + " producto", "error");
      }
    })
    .catch(() => notify("Error de conexión", "error"));
}

function editarProducto(btn) {
  modo = "editar";
  cargarCategorias();
  document.getElementById("modal").classList.add("show");
  document.getElementById("id").value = btn.dataset.id;
  document.getElementById("categoria_id").value = btn.dataset.categoriaId;
  document.getElementById("producto").value = btn.dataset.producto;
  document.getElementById("precio").value = btn.dataset.precio;
  setTimeout(() => {
    let sel = document.getElementById("categoria");
    sel.value = btn.dataset.categoriaId;
  }, 100);
}

function eliminar(id) {
  if (!confirm("¿Eliminar producto?")) return;
  fetch("eliminar_productos.php", {
    method: "POST",
    body: JSON.stringify({ id })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Producto eliminado", "success");
        cargarProductos();
      } else {
        notify("Error al eliminar", "error");
      }
    })
    .catch(() => notify("Error de conexión", "error"));
}

// ===================== VENTAS (POS) =====================
function buscarProductos() {
  let q = document.getElementById("buscarProducto").value;
  fetch("obtener_productos_venta.php?q=" + q)
    .then(r => r.json())
    .then(data => {
      let html = "";
      data.forEach(p => {
        html += `<div class="producto-card" onclick='agregarCarrito(${JSON.stringify(p)})'>
          <div class="producto-nombre">${p.producto}</div>
          <div class="producto-precio">${p.precio} Bs</div>
        </div>`;
      });
      document.getElementById("resultados").innerHTML = html;
    });
}

function agregarCarrito(p) {
  let item = carrito.find(i => i.id == p.id);
  if (item) {
    item.cantidad++;
    notify(p.producto + " x" + item.cantidad, "info");
  } else {
    carrito.push({
      id: p.id,
      producto: p.producto,
      precio: parseFloat(p.precio),
      cantidad: 1,
      descuento: 0
    });
    notify(p.producto + " agregado", "info");
  }
  renderCarrito();
}

function renderCarrito() {
  let html = "";
  let total = 0;
  carrito.forEach((p, index) => {
    let precio_final = p.precio - p.descuento;
    let subtotal = precio_final * p.cantidad;
    total += subtotal;
    html += `<div class="carrito-item">
      <div class="item-info">
        <span class="item-nombre">${p.producto}</span>
        <span class="item-detalle">Cant: ${p.cantidad} x ${p.precio} Bs</span>
        <div class="item-descuento">
          <label>Dto:</label>
          <input type="number" class="descuento-input" value="${p.descuento}"
            onchange="actualizarDescuento(${index}, this.value)" min="0" max="${p.precio}" step="0.01">
          <label>Bs</label>
        </div>
      </div>
      <div class="item-acciones">
        <span class="item-subtotal">${subtotal} Bs</span>
        <button class="btn-eliminar-item" onclick="eliminarItem(${index})">&times;</button>
      </div>
    </div>`;
  });
  document.getElementById("carrito").innerHTML = html;
  document.getElementById("total").innerText = total;
}

function actualizarDescuento(index, valor) {
  let d = parseFloat(valor) || 0;
  if (d > carrito[index].precio) d = carrito[index].precio;
  if (d < 0) d = 0;
  carrito[index].descuento = d;
  renderCarrito();
}

function eliminarItem(i) {
  let nombre = carrito[i].producto;
  carrito.splice(i, 1);
  renderCarrito();
  notify(nombre + " eliminado del carrito", "info");
}

function finalizarVenta() {
  if (carrito.length === 0) {
    notify("El carrito está vacío", "error");
    return;
  }
  let metodo = document.querySelector('input[name="pago"]:checked').value;
  let estado = document.querySelector('input[name="estado"]:checked').value;
  let cliente_nombre = document.getElementById("clienteNombre").value || null;
  let total = document.getElementById("total").innerText;
  fetch("guardar_venta.php", {
    method: "POST",
    body: JSON.stringify({
      metodo_pago: metodo,
      estado: estado,
      cliente_nombre: cliente_nombre,
      carrito: carrito,
      total: total
    })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Venta registrada — Total: " + total + " Bs", "success");
        carrito = [];
        renderCarrito();
      } else {
        notify("Error al registrar venta", "error");
      }
    })
    .catch(() => notify("Error de conexión", "error"));
}

// ===================== HISTORIAL =====================
function cargarHistorial() {
  fetch("obtener_ventas.php")
    .then(r => r.json())
    .then(data => {
      let html = "";
      let total = 0, efectivo = 0, qr = 0;
      data.forEach(v => {
        total += parseFloat(v.total);
        if (v.metodo_pago === "EFECTIVO") efectivo += parseFloat(v.total);
        else qr += parseFloat(v.total);
        html += `<tr>
          <td>${v.id}</td>
          <td>${v.fecha}</td>
          <td>${v.cliente_nombre || "-"}</td>
          <td>${v.metodo_pago}</td>
          <td>${v.total} Bs</td>
          <td>${v.estado === "POR_COBRAR" ? '<span style="color:var(--warning);font-weight:600;">Por cobrar</span>' : "Pagado"}</td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="verDetalle(${v.id})">Ver</button>
            <button class="btn btn-sm btn-secondary" onclick="mostrarEditarVenta(${v.id})">Editar</button>
          </td>
        </tr>`;
      });
      document.getElementById("tablaHistorial").innerHTML = html;
      document.getElementById("resumen").innerHTML = `
        <div class="resumen-card"><div class="resumen-label">Total Ventas</div><div class="resumen-valor">${total} Bs</div></div>
        <div class="resumen-card"><div class="resumen-label">Efectivo</div><div class="resumen-valor">${efectivo} Bs</div></div>
        <div class="resumen-card"><div class="resumen-label">QR</div><div class="resumen-valor">${qr} Bs</div></div>`;
    });
}

function verDetalle(id) {
  fetch("detalle_venta.php?id=" + id)
    .then(r => r.json())
    .then(data => {
      let v = data.venta;
      let html = "<h3>Venta #" + v.id + "</h3>";
      html += "<p><strong>Cliente:</strong> " + (v.cliente_nombre || "-") + "</p>";
      html += "<p><strong>Estado:</strong> " + (v.estado === "POR_COBRAR" ? "Por cobrar" : "Pagado") + "</p>";
      html += "<p><strong>Método:</strong> " + v.metodo_pago + "</p>";
      html += "<p><strong>Total:</strong> " + v.total + " Bs</p>";
      html += "<hr>";
      data.detalle.forEach(d => {
        html += `<p>${d.producto} x${d.cantidad} a ${d.precio} Bs = ${d.subtotal} Bs</p>`;
      });
      document.getElementById("detalleVenta").innerHTML = html;
    });
}

function mostrarEditarVenta(id) {
  fetch("detalle_venta.php?id=" + id)
    .then(r => r.json())
    .then(data => {
      let v = data.venta;
      document.getElementById("editId").value = v.id;
      document.getElementById("editVentaId").textContent = v.id;
      document.getElementById("editCliente").value = v.cliente_nombre || "";
      document.getElementById(v.metodo_pago === "EFECTIVO" ? "editPagoEfectivo" : "editPagoQR").checked = true;
      document.getElementById(v.estado === "PAGADO" ? "editEstadoPagado" : "editEstadoPorCobrar").checked = true;
      document.getElementById("modalEditarVenta").classList.add("show");
    });
}

function guardarEditarVenta() {
  let id = document.getElementById("editId").value;
  let cliente_nombre = document.getElementById("editCliente").value || null;
  let metodo_pago = document.querySelector('input[name="editPago"]:checked')?.value;
  let estado = document.querySelector('input[name="editEstado"]:checked')?.value;

  if (!metodo_pago || !estado) { notify("Complete todos los campos", "error"); return; }

  fetch("editar_venta.php", {
    method: "POST",
    body: JSON.stringify({ id, cliente_nombre, metodo_pago, estado })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Venta actualizada", "success");
        cerrarEditarVenta();
        cargarHistorial();
      } else {
        notify("Error al actualizar", "error");
      }
    });
}

function cerrarEditarVenta() {
  document.getElementById("modalEditarVenta").classList.remove("show");
}

// ===================== CIERRE DE CAJA (delegado a caja.js) =====================
function cargarCierre() {
  if (typeof cargarCajaActual === "function") {
    cargarCajaActual();
  }
}

// ===================== GUARDA EQUIPAJE =====================
function cargarGuardas() {
  fetch("obtener_guardas.php")
    .then(r => r.json())
    .then(data => {
      let html = "";
      data.forEach(g => {
        html += `<tr>
          <td>${g.id}</td>
          <td>${g.nombre_completo}</td>
          <td>${g.cedula_identidad}</td>
          <td>${g.fecha_recojo}</td>
          <td>${g.monto} Bs</td>
          <td>${g.metodo_pago}</td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="verDetalleGuarda(${g.id})">Ver</button>
            <button class="btn btn-sm btn-danger" onclick="eliminarGuarda(${g.id})">Eliminar</button>
          </td>
        </tr>`;
      });
      document.getElementById("tablaGuardas").innerHTML = html;
    });
}

function guardarGuarda() {
  let data = {
    nombre_completo: document.getElementById("gNombre").value,
    cedula_identidad: document.getElementById("gCedula").value,
    equipaje: document.getElementById("gEquipaje").value,
    fecha_recojo: document.getElementById("gFechaRecojo").value,
    monto: document.getElementById("gMonto").value,
    metodo_pago: document.querySelector('input[name="gPago"]:checked').value
  };
  if (!data.nombre_completo || !data.cedula_identidad || !data.equipaje || !data.fecha_recojo || !data.monto) {
    notify("Complete todos los campos", "error");
    return;
  }
  fetch("guardar_guarda.php", {
    method: "POST",
    body: JSON.stringify(data)
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Guarda equipaje registrado", "success");
        document.getElementById("formGuarda").reset();
        cargarGuardas();
      } else {
        notify("Error al registrar", "error");
      }
    })
    .catch(() => notify("Error de conexión", "error"));
}

function verDetalleGuarda(id) {
  fetch("obtener_guardas.php?id=" + id)
    .then(r => r.json())
    .then(g => {
      let html = `<h3>Detalle de Guarda Equipaje</h3>
        <p><strong>Nombre:</strong> ${g.nombre_completo}</p>
        <p><strong>Cédula:</strong> ${g.cedula_identidad}</p>
        <p><strong>Equipaje:</strong><br>${g.equipaje.replace(/\n/g, "<br>")}</p>
        <p><strong>Recojo:</strong> ${g.fecha_recojo}</p>
        <p><strong>Monto:</strong> ${g.monto} Bs</p>
        <p><strong>Método de pago:</strong> ${g.metodo_pago}</p>
        <p><strong>Registrado:</strong> ${g.fecha_creacion}</p>`;
      document.getElementById("detalleGuarda").innerHTML = html;
    });
}

function eliminarGuarda(id) {
  if (!confirm("¿Eliminar este registro de guarda equipaje?")) return;
  fetch("eliminar_guarda.php", {
    method: "POST",
    body: JSON.stringify({ id })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Registro eliminado", "success");
        cargarGuardas();
      } else {
        notify("Error al eliminar", "error");
      }
    })
    .catch(() => notify("Error de conexión", "error"));
}


