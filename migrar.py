import sqlite3, os

DB = os.path.join(os.path.dirname(__file__), "base.db")
BACKUP = os.path.join(os.path.dirname(__file__), "base_backup.db")

# Backup original
if os.path.exists(BACKUP):
    os.remove(BACKUP)
os.rename(DB, BACKUP)
print("Respaldado base.db -> base_backup.db")

old = sqlite3.connect(BACKUP)
new = sqlite3.connect(DB)
c = new.cursor()

# Crear tabla categorias
c.executescript("""
CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria_id INTEGER,
    producto TEXT NOT NULL,
    precio_detalle REAL NOT NULL,
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
""")

# Migrar productos
try:
    old_rows = old.execute("SELECT id, categoria, producto, precio_detalle FROM productos").fetchall()
    print(f"Productos encontrados: {len(old_rows)}")

    categorias_map = {}
    migrados = 0

    for pid, cat_nombre, prod, precio in old_rows:
        cat_nombre = cat_nombre.strip() if cat_nombre else "General"
        if cat_nombre not in categorias_map:
            existe = new.execute("SELECT id FROM categorias WHERE nombre = ?", (cat_nombre,)).fetchone()
            if existe:
                categorias_map[cat_nombre] = existe[0]
            else:
                cur = new.execute("INSERT INTO categorias (nombre) VALUES (?)", (cat_nombre,))
                categorias_map[cat_nombre] = cur.lastrowid
        cid = categorias_map[cat_nombre]
        new.execute(
            "INSERT INTO productos (id, categoria_id, producto, precio_detalle) VALUES (?, ?, ?, ?)",
            (pid, cid, prod, precio)
        )
        migrados += 1

    # Migrar ventas y detalle_ventas si existen
    for tbl in ["ventas", "detalle_ventas"]:
        try:
            rows = old.execute(f"SELECT * FROM {tbl}").fetchall()
            if rows:
                cols = [d[0] for d in old.execute(f"PRAGMA table_info({tbl})").fetchall()]
                placeholders = ",".join(["?" for _ in cols])
                colnames = ",".join(cols)
                for r in rows:
                    new.execute(f"INSERT INTO {tbl} ({colnames}) VALUES ({placeholders})", r)
                print(f"Migrados {len(rows)} registros de {tbl}")
        except Exception as e:
            print(f"  {tbl}: {e}")

    new.commit()
    print(f"\nMigración completa: {migrados} productos, {len(categorias_map)} categorías")
    print("Categorías:", list(categorias_map.keys()))

except Exception as e:
    new.rollback()
    print("Error:", e)
finally:
    old.close()
    new.close()
