import sqlite3, os, shutil
from datetime import datetime, timedelta

DB = os.path.join(os.path.dirname(__file__), "base.db")
BACKUP = os.path.join(os.path.dirname(__file__), "base_backup.db")

shutil.copy2(DB, BACKUP)
print(f"Backup creado: {BACKUP}")

db = sqlite3.connect(DB)
c = db.cursor()

# Corregir ventas.fecha
rows = c.execute("SELECT id, fecha FROM ventas").fetchall()
for rid, fecha in rows:
    if fecha:
        try:
            dt = datetime.strptime(fecha, "%Y-%m-%d %H:%M:%S")
            dt_lp = dt - timedelta(hours=4)
            c.execute("UPDATE ventas SET fecha = ? WHERE id = ?", (dt_lp.strftime("%Y-%m-%d %H:%M:%S"), rid))
        except ValueError:
            pass

# Corregir equipajes.fecha_creacion
rows = c.execute("SELECT id, fecha_creacion FROM equipajes").fetchall()
for rid, fecha in rows:
    if fecha:
        try:
            dt = datetime.strptime(fecha, "%Y-%m-%d %H:%M:%S")
            dt_lp = dt - timedelta(hours=4)
            c.execute("UPDATE equipajes SET fecha_creacion = ? WHERE id = ?", (dt_lp.strftime("%Y-%m-%d %H:%M:%S"), rid))
        except ValueError:
            pass

db.commit()
db.close()
print("Corrección de huso horario completada (UTC -> La Paz, UTC-4)")
