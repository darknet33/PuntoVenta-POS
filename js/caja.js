let cajaActual = null;
let pdfDoc = null;

function cargarCierre() {
  cargarCajaActual();
}

function cargarCajaActual() {
  fetch("obtener_caja_actual.php")
    .then(r => r.json())
    .then(data => {
      cajaActual = data.caja;
      let html = "";

      if (!cajaActual) {
        html = renderInicioCajaForm();
      } else {
        html = renderCajaAbierta(data);
      }

      document.getElementById("cajaContent").innerHTML = html;

      if (cajaActual && cajaActual.estado === "ABIERTA") {
        renderCierreSection(data);
      }
    });
}

function renderInicioCajaForm() {
  let now = new Date();
  let iso = now.getFullYear() + "-" +
    String(now.getMonth() + 1).padStart(2, "0") + "-" +
    String(now.getDate()).padStart(2, "0") + "T" +
    String(now.getHours()).padStart(2, "0") + ":" +
    String(now.getMinutes()).padStart(2, "0");

  return `<div class="panel">
    <h2>Inicio de Caja</h2>
    <div class="caja-form-grid">
      <div class="reporte-field">
        <label>Fecha y Hora de Inicio</label>
        <input type="datetime-local" id="cajaFechaInicio" value="${iso}">
      </div>
      <div class="reporte-field">
        <label>Turno</label>
        <div class="reporte-opciones">
          <label class="reporte-opcion">
            <input type="radio" name="cajaTurno" value="DIA" checked> Día
          </label>
          <label class="reporte-opcion">
            <input type="radio" name="cajaTurno" value="TARDE"> Tarde
          </label>
          <label class="reporte-opcion">
            <input type="radio" name="cajaTurno" value="NOCHE"> Noche
          </label>
        </div>
      </div>
      <div class="reporte-field">
        <label>Encargado</label>
        <input type="text" id="cajaEncargado" placeholder="Nombre del encargado">
      </div>
      <div class="reporte-field">
        <label>Monto Inicial (Bs)</label>
        <input type="number" id="cajaMontoInicial" placeholder="0.00" step="0.01">
      </div>
    </div>
    <button class="btn btn-secondary" onclick="abrirCaja()" style="margin-top:16px;">Abrir Caja</button>
  </div>`;
}

function renderCajaAbierta(data) {
  let c = cajaActual;
  return `<div class="panel">
    <div class="flex items-center justify-between gap-4" style="flex-wrap:wrap;">
      <h2 style="margin:0;">Caja <span class="caja-badge abierta">ABIERTA</span></h2>
      <button class="btn btn-ghost btn-sm" onclick="editarApertura()">Editar</button>
    </div>
    <div class="caja-info-grid">
      <div class="caja-info-item">
        <span class="caja-info-label">Inicio</span>
        <span class="caja-info-value">${c.fecha_inicio}</span>
      </div>
      <div class="caja-info-item">
        <span class="caja-info-label">Turno</span>
        <span class="caja-info-value">${c.turno}</span>
      </div>
      <div class="caja-info-item">
        <span class="caja-info-label">Encargado</span>
        <span class="caja-info-value">${c.encargado}</span>
      </div>
      <div class="caja-info-item">
        <span class="caja-info-label">Monto Inicial</span>
        <span class="caja-info-value">${parseFloat(c.monto_inicial).toFixed(2)} Bs</span>
      </div>
    </div>

    <hr>

    <h3>Reporte / Consulta</h3>
    <button class="btn btn-primary" onclick="vistaPreviaPDF(${c.id})">Vista Previa PDF</button>

    <hr>

    <h3>Cierre de Caja</h3>
    <div id="cierreResumenCards"></div>
    <div id="cierreCortesSection"></div>
  </div>`;
}

function renderCierreSection(data) {
  let ef = parseFloat(data.ventas_efectivo || 0);
  let eq = parseFloat(data.ventas_qr || 0);
  let ge = parseFloat(data.equipaje_efectivo || 0);
  let gq = parseFloat(data.equipaje_qr || 0);
  let mi = parseFloat(cajaActual.monto_inicial || 0);
  let efEsp = mi + ef + ge;

  document.getElementById("cierreResumenCards").innerHTML = `
    <div class="cierre-summary">
      <div class="cierre-card"><div class="cierre-label">Ventas Efectivo</div><div class="cierre-valor">${ef.toFixed(2)} Bs</div></div>
      <div class="cierre-card"><div class="cierre-label">Ventas QR</div><div class="cierre-valor">${eq.toFixed(2)} Bs</div></div>
      <div class="cierre-card"><div class="cierre-label">Guarda Eq. Efectivo</div><div class="cierre-valor">${ge.toFixed(2)} Bs</div></div>
      <div class="cierre-card"><div class="cierre-label">Guarda Eq. QR</div><div class="cierre-valor">${gq.toFixed(2)} Bs</div></div>
      <div class="cierre-card"><div class="cierre-label">Monto Inicial</div><div class="cierre-valor">${mi.toFixed(2)} Bs</div></div>
      <div class="cierre-card warning"><div class="cierre-label">Efectivo Esperado</div><div class="cierre-valor">${efEsp.toFixed(2)} Bs</div></div>
    </div>`;

  document.getElementById("cierreCortesSection").innerHTML = `
    <h3>Cortes</h3>
    <div class="cortes-grid">
      ${[
        [200, "corte_200"], [100, "corte_100"], [50, "corte_50"],
        [20, "corte_20"], [10, "corte_10"], [5, "corte_5"],
        [2, "corte_2"], [1, "corte_1"]
      ].map(([denom, id]) => `<div class="cortes-row">
        <span class="cortes-label">${denom} Bs</span>
        <input type="number" class="cortes-input" id="${id}" min="0" value="0" oninput="calcularCortes()">
        <span class="cortes-subtotal" id="${id}_sub">0 Bs</span>
      </div>`).join("")}
      ${[
        [0.5, "corte_05"], [0.2, "corte_02"], [0.1, "corte_01"]
      ].map(([denom, id]) => `<div class="cortes-row">
        <span class="cortes-label">${denom.toFixed(1)} Bs</span>
        <input type="number" class="cortes-input" id="${id}" min="0" value="0" step="0.1" oninput="calcularCortes()">
        <span class="cortes-subtotal" id="${id}_sub">0 Bs</span>
      </div>`).join("")}
      <div class="cortes-row cortes-total">
        <span class="cortes-label">Total Efectivo</span>
        <span></span>
        <span class="cortes-subtotal" id="corteTotal">0 Bs</span>
      </div>
    </div>

    <div class="reporte-field" style="margin-top:16px;">
      <label>QR Real (Bs)</label>
      <input type="number" id="qrReal" placeholder="0.00" step="0.01" oninput="calcularCortes()">
    </div>

    <button class="btn btn-primary" onclick="calcularCierre()" style="margin-top:12px;">Calcular Cierre</button>
    <div id="resultadoCierre"></div>
    <button class="btn btn-secondary" id="btnCerrarCaja" style="display:none;margin-top:12px;" onclick="cerrarCaja(${cajaActual.id})">Cerrar Caja y Generar PDF Final</button>
  `;
}

function editarApertura() {
  let c = cajaActual;
  let panel = document.querySelector(".panel");
  let info = panel.querySelector(".caja-info-grid");
  if (!info) return;

  let html = `<div class="caja-form-grid">
    <div class="reporte-field">
      <label>Fecha y Hora de Inicio</label>
      <input type="datetime-local" id="cajaFechaInicio" value="${c.fecha_inicio}">
    </div>
    <div class="reporte-field">
      <label>Turno</label>
      <div class="reporte-opciones">
        <label class="reporte-opcion${c.turno === "DIA" ? " checked" : ""}">
          <input type="radio" name="cajaTurno" value="DIA" ${c.turno === "DIA" ? "checked" : ""}> Día
        </label>
        <label class="reporte-opcion${c.turno === "TARDE" ? " checked" : ""}">
          <input type="radio" name="cajaTurno" value="TARDE" ${c.turno === "TARDE" ? "checked" : ""}> Tarde
        </label>
        <label class="reporte-opcion${c.turno === "NOCHE" ? " checked" : ""}">
          <input type="radio" name="cajaTurno" value="NOCHE" ${c.turno === "NOCHE" ? "checked" : ""}> Noche
        </label>
      </div>
    </div>
    <div class="reporte-field">
      <label>Encargado</label>
      <input type="text" id="cajaEncargado" value="${c.encargado}">
    </div>
    <div class="reporte-field">
      <label>Monto Inicial (Bs)</label>
      <input type="number" id="cajaMontoInicial" value="${c.monto_inicial}" step="0.01">
    </div>
  </div>
  <button class="btn btn-primary" onclick="actualizarApertura(${c.id})" style="margin-top:16px;">Guardar Cambios</button>
  <button class="btn btn-ghost" onclick="cargarCajaActual()" style="margin-top:8px;">Cancelar</button>`;

  info.outerHTML = html;
}

function abrirCaja() {
  let fecha_inicio = document.getElementById("cajaFechaInicio").value;
  let turno = document.querySelector('input[name="cajaTurno"]:checked')?.value;
  let encargado = document.getElementById("cajaEncargado").value;
  let monto_inicial = document.getElementById("cajaMontoInicial").value;

  if (!fecha_inicio || !turno || !encargado) {
    notify("Complete todos los campos", "error");
    return;
  }

  fetch("guardar_apertura_caja.php", {
    method: "POST",
    body: JSON.stringify({ fecha_inicio, turno, encargado, monto_inicial })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Caja abierta exitosamente", "success");
        cargarCajaActual();
      } else {
        notify(res.message || "Error al abrir caja", "error");
      }
    });
}

function actualizarApertura(id) {
  let fecha_inicio = document.getElementById("cajaFechaInicio").value;
  let turno = document.querySelector('input[name="cajaTurno"]:checked')?.value;
  let encargado = document.getElementById("cajaEncargado").value;
  let monto_inicial = document.getElementById("cajaMontoInicial").value;

  fetch("guardar_apertura_caja.php", {
    method: "POST",
    body: JSON.stringify({ id, fecha_inicio, turno, encargado, monto_inicial })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Caja actualizada", "success");
        cargarCajaActual();
      } else {
        notify(res.message || "Error al actualizar", "error");
      }
    });
}

function calcularCortes() {
  let denominaciones = [
    { id: "corte_200", val: 200 }, { id: "corte_100", val: 100 },
    { id: "corte_50", val: 50 }, { id: "corte_20", val: 20 },
    { id: "corte_10", val: 10 }, { id: "corte_5", val: 5 },
    { id: "corte_2", val: 2 }, { id: "corte_1", val: 1 },
    { id: "corte_05", val: 0.5 }, { id: "corte_02", val: 0.2 },
    { id: "corte_01", val: 0.1 }
  ];

  let total = 0;
  denominaciones.forEach(d => {
    let input = document.getElementById(d.id);
    let cant = parseFloat(input.value) || 0;
    let sub = cant * d.val;
    total += sub;
    let el = document.getElementById(d.id + "_sub");
    if (el) el.textContent = sub.toFixed(2) + " Bs";
  });

  document.getElementById("corteTotal").textContent = total.toFixed(2) + " Bs";
}

function calcularCierre() {
  let inputs = document.querySelectorAll(".cortes-input");
  let cortes = {};
  inputs.forEach(inp => {
    cortes[inp.id] = parseFloat(inp.value) || 0;
  });
  let qrReal = parseFloat(document.getElementById("qrReal")?.value) || 0;

  let totalCortes = [
    [200, cortes.corte_200 || 0], [100, cortes.corte_100 || 0],
    [50, cortes.corte_50 || 0], [20, cortes.corte_20 || 0],
    [10, cortes.corte_10 || 0], [5, cortes.corte_5 || 0],
    [2, cortes.corte_2 || 0], [1, cortes.corte_1 || 0],
    [0.5, cortes.corte_05 || 0], [0.2, cortes.corte_02 || 0],
    [0.1, cortes.corte_01 || 0]
  ].reduce((sum, [v, c]) => sum + v * c, 0);

  fetch("obtener_caja_actual.php")
    .then(r => r.json())
    .then(data => {
      let efEsp = parseFloat(data.efectivo_esperado || 0);
      let qrSis = parseFloat(data.qr_sistema || 0);
      let difEf = totalCortes - efEsp;
      let difQr = qrReal - qrSis;

      let cls = "";
      let estado = "";
      if (difEf > 0) { estado = "SOBRANTE"; cls = "warning"; }
      else if (difEf < 0) { estado = "FALTANTE"; cls = "danger"; }
      else { estado = "CUADRADO"; cls = "success"; }

      document.getElementById("resultadoCierre").innerHTML = `
        <hr>
        <div class="cierre-summary">
          <div class="cierre-card"><div class="cierre-label">Total Cortes</div><div class="cierre-valor">${totalCortes.toFixed(2)} Bs</div></div>
          <div class="cierre-card ${cls}"><div class="cierre-label">Efectivo Esperado</div><div class="cierre-valor">${efEsp.toFixed(2)} Bs</div></div>
          <div class="cierre-card ${cls}"><div class="cierre-label">Dif. Efectivo</div><div class="cierre-valor">${difEf.toFixed(2)} Bs</div></div>
          <div class="cierre-card"><div class="cierre-label">QR Real</div><div class="cierre-valor">${qrReal.toFixed(2)} Bs</div></div>
          <div class="cierre-card"><div class="cierre-label">QR Sistema</div><div class="cierre-valor">${qrSis.toFixed(2)} Bs</div></div>
          <div class="cierre-card"><div class="cierre-label">Dif. QR</div><div class="cierre-valor">${difQr.toFixed(2)} Bs</div></div>
        </div>
        <div style="text-align:center;margin-top:12px;">
          <span class="estado-badge ${cls}">${estado} (${difEf.toFixed(2)} Bs)</span>
        </div>`;

      notify("Cierre calculado: " + estado, cls === "danger" ? "error" : cls === "warning" ? "info" : "success");
      document.getElementById("btnCerrarCaja").style.display = "inline-flex";
    });
}

function cerrarCaja(cajaId) {
  if (!confirm("¿Está seguro de cerrar la caja? Se generará el PDF final.")) return;

  let inputs = document.querySelectorAll(".cortes-input");
  let data = { caja_id: cajaId };
  inputs.forEach(inp => { data[inp.id] = parseFloat(inp.value) || 0; });
  data.qr_real = parseFloat(document.getElementById("qrReal")?.value) || 0;

  fetch("cerrar_caja.php", {
    method: "POST",
    body: JSON.stringify(data)
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === "ok") {
        notify("Caja cerrada exitosamente", "success");
        generarPDFFinal(cajaId, true);
      } else {
        notify(res.message || "Error al cerrar caja", "error");
      }
    });
}

let pdfBlobUrl = null;

function vistaPreviaPDF(cajaId) {
  fetch("obtener_reporte_completo.php?caja_id=" + cajaId)
    .then(r => r.json())
    .then(r => {
      pdfDoc = generarPDFDoc(r);
      pdfBlobUrl = pdfDoc.output("bloburl");
      document.getElementById("pdfFrame").src = pdfBlobUrl;
      document.getElementById("modalPdfPreview").classList.add("show");
    });
}

function descargarPDF() {
  if (pdfDoc) {
    pdfDoc.save("reporte_ventas_" + new Date().toISOString().slice(0, 10) + ".pdf");
  }
}

function cerrarModalPdf() {
  document.getElementById("modalPdfPreview").classList.remove("show");
  document.getElementById("pdfFrame").src = "";
  if (pdfBlobUrl) { URL.revokeObjectURL(pdfBlobUrl); pdfBlobUrl = null; }
}

function generarPDFFinal(cajaId, descargar) {
  fetch("obtener_reporte_completo.php?caja_id=" + cajaId)
    .then(r => r.json())
    .then(r => {
      let doc = generarPDFDoc(r);
      if (descargar) {
        doc.save("reporte_final_caja_" + cajaId + "_" + new Date().toISOString().slice(0, 10) + ".pdf");
      }
      setTimeout(() => cargarCajaActual(), 500);
    });
}

function generarPDFDoc(r) {
  let { jsPDF } = window.jspdf;
  let doc = new jsPDF({ unit: "mm", format: "a4" });
  let x = 10;
  let y = 15;
  let pageW = 190;

  function bold(s) { doc.setFont("helvetica", "bold"); doc.setFontSize(s); }
  function normal(s) { doc.setFont("helvetica", "normal"); doc.setFontSize(s); }

  let caja = r.caja || {};
  let turno = (caja.turno || "DIA").charAt(0) + (caja.turno || "DIA").slice(1).toLowerCase();
  let encargado = caja.encargado || "-";
  let desde = caja.fecha_inicio || r.fecha || "";
  let hasta = caja.fecha_cierre || "En curso";

  bold(18); doc.text("REPORTE DE VENTAS", x, y); y += 8;
  normal(10);
  doc.text("Desde: " + desde, x, y); y += 5;
  doc.text("Hasta: " + hasta, x, y); y += 5;
  doc.text("Turno: " + turno, x, y); y += 5;
  doc.text("Encargado: " + encargado, x, y); y += 10;

  if (r.ventas && r.ventas.length > 0) {
    bold(14); doc.text("VENTAS", x, y); y += 7;
    let body = [];
    r.ventas.forEach(v => {
      (v.detalle || []).forEach(d => {
        body.push([d.producto || "", String(d.cantidad || 0), parseFloat(d.precio || 0).toFixed(2), parseFloat(d.subtotal || 0).toFixed(2)]);
      });
    });
    doc.autoTable({
      startY: y, head: [["Producto", "Cant.", "Precio", "Subtotal"]], body,
      theme: "grid", headStyles: { fillColor: [108, 99, 255], fontSize: 9, halign: "center" },
      bodyStyles: { fontSize: 8 },
      columnStyles: { 0: { cellWidth: 70 }, 1: { halign: "center", cellWidth: 20 }, 2: { halign: "right", cellWidth: 30 }, 3: { halign: "right", cellWidth: 30 } },
      margin: { left: x, right: x }
    });
    y = doc.lastAutoTable.finalY + 6;
    normal(10);
    doc.text("Efectivo: " + parseFloat(r.ventas_efectivo || 0).toFixed(2) + " Bs", x, y); y += 4;
    doc.text("QR: " + parseFloat(r.ventas_qr || 0).toFixed(2) + " Bs", x, y); y += 4;
    bold(11); doc.text("Total Recaudado: " + parseFloat(r.total_ventas || 0).toFixed(2) + " Bs", x, y); y += 10;
  }

  if (r.equipajes && r.equipajes.length > 0) {
    if (y > 200) { doc.addPage(); y = 20; }
    bold(14); doc.text("GUARDA EQUIPAJE", x, y); y += 7;
    let bodyEq = [];
    r.equipajes.forEach(e => {
      bodyEq.push([e.nombre_completo || "", e.cedula_identidad || "", e.equipaje || "", e.fecha_recojo || "", parseFloat(e.monto || 0).toFixed(2), e.metodo_pago || ""]);
    });
    doc.autoTable({
      startY: y, head: [["Cliente", "CI", "Equipaje", "Fecha/Hora", "Precio", "Pago"]], body: bodyEq,
      theme: "grid", headStyles: { fillColor: [0, 212, 170], fontSize: 8, halign: "center" },
      bodyStyles: { fontSize: 7 },
      columnStyles: { 0: { cellWidth: 30 }, 1: { cellWidth: 20 }, 2: { cellWidth: 45 }, 3: { cellWidth: 30 }, 4: { halign: "right", cellWidth: 18 }, 5: { halign: "center", cellWidth: 15 } },
      margin: { left: x, right: x }
    });
    y = doc.lastAutoTable.finalY + 6;
    normal(10);
    doc.text("Efectivo: " + parseFloat(r.equipaje_efectivo || 0).toFixed(2) + " Bs", x, y); y += 4;
    doc.text("QR: " + parseFloat(r.equipaje_qr || 0).toFixed(2) + " Bs", x, y); y += 4;
    bold(11); doc.text("Total Recaudado: " + parseFloat(r.total_equipajes || 0).toFixed(2) + " Bs", x, y); y += 10;
  }

  if (y > 210) { doc.addPage(); y = 20; }
  doc.setDrawColor(200, 200, 200); doc.line(x, y, x + pageW, y); y += 6;
  bold(12); doc.text("RESUMEN", x, y); y += 8;

  let ve = parseFloat(r.ventas_efectivo || 0);
  let vq = parseFloat(r.ventas_qr || 0);
  let ge = parseFloat(r.equipaje_efectivo || 0);
  let gq = parseFloat(r.equipaje_qr || 0);
  let mi = parseFloat(caja.monto_inicial || 0);

  doc.autoTable({
    startY: y, head: [["", "Efectivo", "QR"]],
    body: [
      ["Ventas", ve.toFixed(2) + " Bs", vq.toFixed(2) + " Bs"],
      ["Guarda Equipaje", ge.toFixed(2) + " Bs", gq.toFixed(2) + " Bs"],
      ["Monto Inicial", mi.toFixed(2) + " Bs", "-"],
      [{ content: "TOTAL", styles: { fontStyle: "bold" } }, (ve + ge + mi).toFixed(2) + " Bs", vq.toFixed(2) + " Bs"]
    ],
    theme: "grid", headStyles: { fillColor: [108, 99, 255], fontSize: 9, halign: "center" },
    bodyStyles: { fontSize: 9 },
    columnStyles: { 0: { cellWidth: 70 }, 1: { halign: "right", cellWidth: 40 }, 2: { halign: "right", cellWidth: 40 } },
    margin: { left: x, right: x }
  });
  y = doc.lastAutoTable.finalY + 8;

  let granTotal = ve + vq + ge + gq + mi;
  doc.setDrawColor(108, 99, 255); doc.line(x, y, x + pageW, y); y += 6;
  doc.setTextColor(108, 99, 255);
  bold(16); doc.text("GRAN TOTAL GENERAL: " + granTotal.toFixed(2) + " Bs", x, y); y += 10;
  doc.setTextColor(0, 0, 0);

  if (caja.total_cortes !== undefined && caja.total_cortes !== null) {
    bold(11); doc.text("Cierre de Caja", x, y); y += 6;
    normal(9);
    doc.text("Total Cortes: " + parseFloat(caja.total_cortes || 0).toFixed(2) + " Bs", x, y); y += 4;
    doc.text("QR Real: " + parseFloat(caja.qr_real || 0).toFixed(2) + " Bs", x, y); y += 4;
    let difLabel = "";
    let d = parseFloat(caja.diferencia || 0);
    if (d > 0) difLabel = "SOBRANTE";
    else if (d < 0) difLabel = "FALTANTE";
    else difLabel = "CUADRADO";
    doc.text("Diferencia: " + d.toFixed(2) + " Bs (" + difLabel + ")", x, y); y += 4;
    doc.text("Cierre: " + (caja.fecha_cierre || "-"), x, y);
  }

  return doc;
}
