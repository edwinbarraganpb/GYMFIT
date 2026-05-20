-- =====================================================================
-- GYMFIT - Esquema SQLite
-- La BD se crea automáticamente al primer request (ver php/config.php)
-- Este archivo es referencia/documentación.
-- =====================================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre          TEXT NOT NULL,
    email           TEXT NOT NULL UNIQUE,
    password_hash   TEXT NOT NULL,
    rol             TEXT NOT NULL CHECK(rol IN ('entrenador', 'cliente')),
    avatar_url      TEXT,
    edad            INTEGER,
    objetivo        TEXT,
    nivel           TEXT,
    creado_en       TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS entrenador_cliente (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    creado_en       TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(entrenador_id, cliente_id)
);

CREATE TABLE IF NOT EXISTS rutinas (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    contenido       TEXT NOT NULL,
    observaciones   TEXT,
    asignada_en     TEXT NOT NULL DEFAULT (datetime('now')),
    actualizada_en  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS mensajes (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    de_usuario_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    para_usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    contenido       TEXT NOT NULL,
    leido           INTEGER NOT NULL DEFAULT 0,
    enviado_en      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS contactos (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre      TEXT NOT NULL,
    email       TEXT NOT NULL,
    mensaje     TEXT NOT NULL,
    enviado_en  TEXT NOT NULL DEFAULT (datetime('now'))
);
