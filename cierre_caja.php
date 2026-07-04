<?php include("config.php"); ?>

<div id="cajaContent"></div>

<div id="modalPdfPreview" class="modal" onclick="if(event.target===this)cerrarModalPdf()">
  <div class="modal-content modal-pdf">
    <div class="modal-pdf-header">
      <h3>Vista Previa — Reporte de Ventas</h3>
      <button class="btn btn-ghost" onclick="cerrarModalPdf()">&times;</button>
    </div>
    <iframe id="pdfFrame" style="width:100%;height:70vh;border:none;border-radius:8px;"></iframe>
    <div class="modal-pdf-footer">
      <button class="btn btn-primary" onclick="descargarPDF()">Descargar PDF</button>
      <button class="btn btn-ghost" onclick="cerrarModalPdf()">Cerrar</button>
    </div>
  </div>
</div>
