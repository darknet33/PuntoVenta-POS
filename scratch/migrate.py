import sqlite3

conn = sqlite3.connect("base.db")
cursor = conn.cursor()

# Ensure products table columns exist
cursor.execute("PRAGMA table_info(productos)")
columns = [col[1] for col in cursor.fetchall()]

if "stock" not in columns:
    cursor.execute("ALTER TABLE productos ADD COLUMN stock INTEGER NOT NULL DEFAULT 0")
    print("Added stock to productos")

if "unidades_por_paquete" not in columns:
    cursor.execute("ALTER TABLE productos ADD COLUMN unidades_por_paquete INTEGER NOT NULL DEFAULT 1")
    print("Added unidades_por_paquete to productos")

# Ensure compras and detalle_compras tables exist
cursor.execute("""
CREATE TABLE IF NOT EXISTS compras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATETIME NOT NULL,
    metodo_pago TEXT NOT NULL,
    total REAL NOT NULL
);
""")
print("Ensured compras table")

cursor.execute("""
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
""")
print("Ensured detalle_compras table")

conn.commit()
conn.close()
