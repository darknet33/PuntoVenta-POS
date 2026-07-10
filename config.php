<?php

date_default_timezone_set('America/La_Paz');

$db = new PDO("sqlite:base.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria_id INTEGER,
    producto TEXT NOT NULL,
    precio_detalle REAL NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    unidades_por_paquete INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE IF NOT EXISTS ventas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo_pago TEXT NOT NULL,
    total REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    venta_id INTEGER,
    producto_id INTEGER,
    producto TEXT,
    precio REAL,
    cantidad INTEGER,
    subtotal REAL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id)
);

CREATE TABLE IF NOT EXISTS equipajes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_completo TEXT NOT NULL,
    cedula_identidad TEXT NOT NULL,
    equipaje TEXT NOT NULL,
    fecha_recojo DATETIME NOT NULL,
    monto REAL NOT NULL,
    metodo_pago TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS caja (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha_inicio DATETIME NOT NULL,
    turno TEXT NOT NULL,
    encargado TEXT NOT NULL,
    monto_inicial REAL NOT NULL DEFAULT 0,
    fecha_cierre DATETIME,
    estado TEXT NOT NULL DEFAULT 'ABIERTA',
    corte_200 INTEGER DEFAULT 0,
    corte_100 INTEGER DEFAULT 0,
    corte_50 INTEGER DEFAULT 0,
    corte_20 INTEGER DEFAULT 0,
    corte_10 INTEGER DEFAULT 0,
    corte_5 INTEGER DEFAULT 0,
    corte_2 INTEGER DEFAULT 0,
    corte_1 INTEGER DEFAULT 0,
    corte_05 REAL DEFAULT 0,
    corte_02 REAL DEFAULT 0,
    corte_01 REAL DEFAULT 0,
    qr_real REAL DEFAULT 0,
    total_cortes REAL DEFAULT 0,
    diferencia REAL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS compras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATETIME NOT NULL,
    metodo_pago TEXT NOT NULL,
    total REAL NOT NULL
);

CREATE TABLE IF NOT EXISTS detalle_compras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    compra_id INTEGER,
    producto_id INTEGER,
    producto TEXT,
    cantidad INTEGER,
    costo REAL,
    subtotal REAL,
    FOREIGN KEY (compra_id) REFERENCES compras(id)
);
");

$cols = $db->query("PRAGMA table_info(ventas)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array("cliente_nombre", $cols)) {
    $db->exec("ALTER TABLE ventas ADD COLUMN cliente_nombre TEXT DEFAULT NULL");
}
if (!in_array("estado", $cols)) {
    $db->exec("ALTER TABLE ventas ADD COLUMN estado TEXT NOT NULL DEFAULT 'PAGADO'");
}

$prodCols = $db->query("PRAGMA table_info(productos)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array("stock", $prodCols)) {
    $db->exec("ALTER TABLE productos ADD COLUMN stock INTEGER NOT NULL DEFAULT 0");
}
if (!in_array("unidades_por_paquete", $prodCols)) {
    $db->exec("ALTER TABLE productos ADD COLUMN unidades_por_paquete INTEGER NOT NULL DEFAULT 1");
}
