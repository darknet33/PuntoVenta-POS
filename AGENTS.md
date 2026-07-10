# PuntoVenta — AGENTS.md

## Stack
- **Vanilla PHP 7+** (no framework), **SQLite** via PDO, **vanilla JS** (ES6)
- No package manager, no build step, no tests, no type checking

## Run locally
```bash
php -S localhost:8000
```

## Architecture
- **`index.php`** — shell. Loads `js/app.js` which fetches PHP views as HTML fragments into `#contenido`
- **`js/app.js`** — SPA routing via `cargarVista(vista)` → `fetch(vista + ".php")`. Menu buttons trigger this.
- **`config.php`** — included by every PHP file. Creates DB connection and runs `CREATE TABLE IF NOT EXISTS` for all 5 tables on every request.
- Views: `ventas.php`, `productos.php`, `historial.php`, `cierre_caja.php`, `guarda_equipaje.php`

## DB (SQLite, `base.db`)
5 tables: `categorias`, `productos`, `ventas`, `detalle_ventas`, `equipajes`
- Schema is managed entirely by `config.php` — no migrations.
- Table creation is idempotent (`IF NOT EXISTS`). Any new column must be added via `ALTER TABLE` manually.

## PHP endpoint patterns
- **POST** — read JSON: `json_decode(file_get_contents("php://input"), true)`
- **GET** — query params: `$_GET["param"]`
- **Response** — always `echo json_encode([...])` with `{"status": "ok"}` or `{"status": "error"}`
- All CRUD ops follow the same pattern: `include config.php → read input → execute → echo json`.
- `guardar_venta.php` wraps INSERTs in a transaction (`beginTransaction/commit/rollBack`).

## JS conventions
- Error handling: `.catch(() => notify("Error de conexión", "error"))` — generic catch-all.
- Notifications via `notify(msg, tipo)` where tipo is `"success"`, `"error"`, or `"info"`.

## Documentation requirement
Every change — new endpoint, DB column, feature, or config — must be documented in this file. If it would be useful for a future agent to know, write it down.

## Guarda Equipaje module (added 2026-07-04)
- **View**: `guarda_equipaje.php` — form (nombre_completo, cedula_identidad, equipaje textarea, fecha_recojo datetime-local, monto, metodo_pago) + history table with filters and details columns
- **Endpoints**:
  - `guardar_guarda.php` — POST, JSON → INSERT INTO equipajes
  - `obtener_guardas.php` — GET, returns all rows (or single row if `?id=` param)
  - `eliminar_guarda.php` — POST, JSON → DELETE by id
  - `entregar_guarda.php` — POST, JSON → UPDATE state to 'ENTREGADO' for specific luggage id
- **Table** `equipajes`: id, nombre_completo, cedula_identidad, equipaje (TEXT), fecha_recojo, monto, metodo_pago, fecha_creacion, estado (TEXT NOT NULL DEFAULT 'PENDIENTE')
- **Cierre de caja** (`obtener_resumen_caja.php`) now also sums equipaje payments by method (EFECTIVO/QR) for the current day.
- **JS functions** in `app.js`: `cargarGuardas()`, `guardarGuarda()`, `verDetalleGuarda(id)`, `eliminarGuarda(id)`, `entregarGuarda(id)`
- **Filters**: History table allows filtering by status (`TODOS`, `PENDIENTE`, `ENTREGADO`).


## Ventas — descuento, cliente y estado (added 2026-07-04)
- **Descuento por producto**: cada item del carrito tiene un input `Dto: [__] Bs`. El `precio` que se guarda en `detalle_ventas` es `precio_base - descuento`. El `subtotal` se recalcula con el precio final.
- **Cliente opcional**: input `#clienteNombre` en el panel del carrito. Se guarda en `ventas.cliente_nombre`.
- **Estado de pago**: radio buttons `PAGADO` (default) / `POR_COBRAR` en el panel del carrito. Se guarda en `ventas.estado`.
- **Columnas nuevas** en `ventas`: `cliente_nombre TEXT DEFAULT NULL`, `estado TEXT NOT NULL DEFAULT 'PAGADO'`. Se crean mediante `ALTER TABLE` en `config.php` con `PRAGMA table_info`, idempotente.
- **`detalle_venta.php`** ahora devuelve `{ venta: {...}, detalle: [...] }` para mostrar cliente, estado y método en el detalle.
- **`cargarHistorial()`** y **`verDetalle()`** en `app.js` muestran las nuevas columnas.
- **`historial.php`** incluye las columnas Cliente y Estado en la tabla.

## Editar venta desde historial (added 2026-07-04)
- **`editar_venta.php`** — endpoint POST que UPDATE cliente_nombre, metodo_pago, estado de una venta por id.
- **`historial.php`** — nuevo botón "Editar" por fila + modal con inputs para cliente, método de pago (EFECTIVO/QR), estado (PAGADO/POR_COBRAR).
- **`js/app.js`** — `mostrarEditarVenta(id)`: fetch detalle, rellena modal. `guardarEditarVenta()`: POST a editar_venta.php, recarga historial. `cerrarEditarVenta()`: cierra modal.

## Timezone (America/La_Paz)
- `config.php` establece `date_default_timezone_set('America/La_Paz')` para PHP.
- SQLite `CURRENT_TIMESTAMP` y `DATE('now')` usan UTC — **no usarlos**.
- Todas las fechas se insertan desde PHP con `date("Y-m-d H:i:s")`:
  - `guardar_venta.php` — `$ahora` para `ventas.fecha`
  - `guardar_guarda.php` — `$ahora` para `equipajes.fecha_creacion`
- Todas las consultas de fecha (ej: cierre de caja) usan `date("Y-m-d")` bindeado como parámetro, no `DATE('now')`.

## UI redesign — Tab bar + layout optimization (added 2026-07-04)
- **`index.php`** — redesigned with fixed bottom tab bar (`<nav class="tabbar">`). 5 tabs: Productos, Historial, Ventas (centered, larger icon), Guarda Eq., Cierre. Each tab uses `data-view` attribute for routing.
- **`js/app.js`** — `setActiveMenu()` replaced by `setActiveTab(id)`. Tab clicks use generic `querySelectorAll(".tab")` loop reading `dataset.view`. New `btnCierre` handler for Cierre de Caja. Added `toggleClearBtn(input)` and `limpiarBusqueda(id, callback)` for search clear buttons.
- **`css/style.css`** — removed old `.menu` styles. New `.tabbar`, `.tab`, `.tab-center` classes. Tab bar is fixed bottom with `backdrop-filter: blur(20px)`, safe-area padding, centered active tab with top indicator dot. New `.search-wrapper` / `.search-clear` for search inputs with clear button.
- **Guarda Equipaje form** — layout optimized for vertical (mobile): form fields stack vertically, textarea has `min-height: 80px`, removed `guarda-field-full` grid class, simplified responsive breakpoints. Form panel has `max-width: 480px` and `justify-self: end` on desktop to prevent horizontal stretching.
- **Search clear button** — `.search-clear` button appears inside search inputs when text is present (`.has-text` class toggled by JS). For `ventas.php` (`#buscarProducto`) and `productos.php` (`#buscador`). Clears value and re-triggers search callback.
- **General responsive** — `.app` wrapper replaces `.contenedor`, has padding-bottom for tab bar clearance. Mobile padding reduced to 12px.

## Módulo Caja — Inicio, Reporte y Cierre (added 2026-07-04)
- **`config.php`** — nueva tabla `caja`: id, fecha_inicio, turno, encargado, monto_inicial, fecha_cierre, estado (ABIERTA/CERRADA), cortes (corte_200..corte_01), qr_real, total_cortes, diferencia
- **`index.php`** — tab "Cierre" renombrado a "Caja"; carga `js/caja.js`
- **Nuevos endpoints**:
  - `guardar_apertura_caja.php` — POST, crea o actualiza apertura de caja (si tiene `id` UPDATE, si no INSERT). Valida que no haya otra caja abierta.
  - `obtener_caja_actual.php` — GET, devuelve la caja ABIERTA (con totales de ventas/equipajes desde `fecha_inicio`) o `{caja: null}`
  - `cerrar_caja.php` — POST, guarda cortes, calcula `total_cortes` y `diferencia = total_cortes - efectivo_esperado`, cierra la caja
- **`obtener_reporte_completo.php`** — ahora recibe `?caja_id=X`, filtra ventas y equipajes desde `fecha_inicio` de esa caja (no por día calendario). Devuelve también datos de la caja.
- **`cierre_caja.php`** — rediseñado con 3 secciones:
  1. **Inicio de Caja**: formulario (fecha_inicio, turno, encargado, monto_inicial) si no hay caja abierta; resumen + botón editar si hay
  2. **Reporte / Consulta**: botón "Vista Previa PDF" que abre modal con iframe del PDF
  3. **Cierre de Caja**: tarjetas resumen (Ventas EF/QR, Guarda Eq EF/QR, Monto Inicial, Efectivo Esperado) + tabla de cortes (200,100,50,20,10,5,2,1,0.5,0.2,0.1 Bs) + QR Real + "Calcular Cierre" + "Cerrar Caja y Generar PDF Final"
- **`js/caja.js`** (nuevo, separado de app.js) — funciones: `cargarCierre()`, `cargarCajaActual()`, `renderInicioCajaForm()`, `renderCajaAbierta()`, `renderCierreSection()`, `abrirCaja()`, `actualizarApertura()`, `editarApertura()`, `calcularCortes()`, `calcularCierre()`, `cerrarCaja()`, `vistaPreviaPDF()`, `descargarPDF()`, `cerrarModalPdf()`, `generarPDFFinal()`, `generarPDFDoc()` (compartida para preview y cierre)
- **CSS**: nuevas clases `.caja-badge`, `.caja-form-grid`, `.caja-info-grid`, `.caja-info-item`, `.cortes-grid`, `.cortes-row`, `.cortes-input`, `.cortes-subtotal`, `.cortes-total`, `.modal-pdf`, `.modal-pdf-header`, `.modal-pdf-footer`

## Stock management (added 2026-07-09)
- **Columna `stock`** en `productos`: `INTEGER NOT NULL DEFAULT 0`. Agregada vía `ALTER TABLE` idempotente en `config.php` con `PRAGMA table_info`.
- **`guardar_productos.php`** y **`editar_productos.php`** — aceptan y guardan campo `stock`.
- **`obtener_productos.php`** — SELECT incluye `p.stock`.
- **`obtener_productos_venta.php`** — SELECT incluye `p.stock`.
- **`guardar_venta.php`** — dentro de la transaction, valida `stock >= cantidad` antes de cada item. Si stock insuficiente, lanza Exception y hace rollback. Si OK, ejecuta `UPDATE productos SET stock = stock - :cantidad WHERE id = :id`.
- **Ventas (POS)** — `buscarProductos()` muestra stock en cada card. Si `stock = 0`, card gris con clase `sin-stock` y sin `onclick`. Si `stock <= 5`, texto naranja (`.stock-low`). `agregarCarrito()` valida stock antes de agregar.
- **Productos admin** — tabla incluye columna Stock con `.stock-badge`. Modal de editar incluye campo Stock.
- **CSS**: `.stock-badge`, `.stock-badge.stock-low`, `.stock-badge.stock-empty`, `.producto-card.sin-stock`, `.producto-stock`

## Módulo Compras (added 2026-07-09)
- **View**: `compras.php` — layout similar a ventas: búsqueda de productos + carrito de compras (producto, cantidad, costo unitario) + método de pago (EFECTIVO/QR) + historial de compras
- **Endpoints**:
  - `guardar_compra.php` — POST, JSON → INSERT en `compras` + `detalle_compras` (dentro de transaction). Por cada item: `UPDATE productos SET stock = stock + :cantidad`.
  - `obtener_compras.php` — GET, retorna historial con `GROUP_CONCAT` de productos.
  - `eliminar_compra.php` — POST, JSON → DELETE compra + detalle, revierte stock (`UPDATE productos SET stock = stock - :cantidad`).
- **Tablas nuevas**:
  - `compras`: id, fecha, metodo_pago, total
  - `detalle_compras`: id, compra_id, producto_id, producto, cantidad, costo, subtotal
- **`index.php`** — nuevo tab "Compras" 📥 entre Productos e Historial (6 tabs total).
- **`js/app.js`** — nuevas funciones: `buscarProductosCompra()`, `agregarCarritoCompra(p)`, `renderCarritoCompra()`, `actualizarCostoCompra()`, `eliminarItemCompra()`, `finalizarCompra()`, `cargarCompras()`, `eliminarCompra(id)`. Variable global `carritoCompra` separada de `carrito`.

## Venta/Compra por paquete (added 2026-07-09)
- **Columna `unidades_por_paquete`** en `productos`: `INTEGER NOT NULL DEFAULT 1`. Si vale 1, el producto se vende individualmente. Si > 1, se vende por paquetes.
- **Toggle "Venta/Compra por paquete"**: switch en ventas.php y compras.php. OFF → comportamiento normal (1 clic = 1 unidad). ON → clic en productos con paquete muestra mini-formulario para elegir cantidad de paquetes.
- **Mini-formulario**: aparece debajo de la card con input de cantidad de paquetes, botón "Agregar", y total de unidades calculado en tiempo real.
- **Stock**: descuenta/suma por unidades totales (no por paquetes).
- **Carrito**: los items de paquete muestran `Cant: 24 (2 paq. x12) a 25 Bs`.
- **CSS**: `.switch`, `.slider`, `.toggle-paquete`, `.mini-form-paquete`, `.producto-pack`, `.con-mini-form`.
- **Corrección de comillas en onclick (2026-07-09)**: Se corrigió un error de sintaxis en `js/app.js` en las funciones `buscarProductos` y `buscarProductosCompra` donde al interpolar `JSON.stringify(p)` dentro de un atributo `onclick="..."` con comillas dobles, se rompía el parseo de HTML. Se reemplazó el atributo con comillas simples `'` y se escaparon las comillas simples internas del producto para evitar errores. También se agregaron detalles de empaque en la visualización del carrito de compras.
- **Toggle visual del mini-formulario (2026-07-09)**: Se mejoró la interactividad cuando la opción de compra/venta por paquete está activa. Ahora, los mini-formularios de los productos con paquetes se ocultan por defecto en la grilla y se muestran al hacer clic sobre la tarjeta del producto, agregando la clase `.activo` al elemento.


