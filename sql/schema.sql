-- =====================================================================
-- GYMFIT - Esquema de base de datos PostgreSQL (pgAdmin)
-- Ejecutar este script en pgAdmin después de crear la base de datos "gymfit"
-- =====================================================================

-- Extensión para encriptar contraseñas
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Tipos enumerados
DO $$ BEGIN
    CREATE TYPE app_role AS ENUM ('entrenador', 'cliente');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- =====================================================================
-- Tabla de usuarios
-- =====================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id              SERIAL PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL,
    email           VARCHAR(160) NOT NULL UNIQUE,
    password_hash   TEXT NOT NULL,
    rol             app_role NOT NULL,
    avatar_url      TEXT,
    edad            INT,
    objetivo        VARCHAR(160),
    nivel           VARCHAR(60),
    creado_en       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);

-- =====================================================================
-- Relación entrenador ↔ cliente
-- =====================================================================
CREATE TABLE IF NOT EXISTS entrenador_cliente (
    id              SERIAL PRIMARY KEY,
    entrenador_id   INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    cliente_id      INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    creado_en       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE(entrenador_id, cliente_id)
);

-- =====================================================================
-- Rutinas asignadas a clientes
-- =====================================================================
CREATE TABLE IF NOT EXISTS rutinas (
    id              SERIAL PRIMARY KEY,
    cliente_id      INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    entrenador_id   INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    contenido       TEXT NOT NULL,            -- Texto/HTML con la rutina
    observaciones   TEXT,
    asignada_en     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizada_en  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_rutinas_cliente ON rutinas(cliente_id);

-- =====================================================================
-- Mensajes entre entrenador y cliente
-- =====================================================================
CREATE TABLE IF NOT EXISTS mensajes (
    id              SERIAL PRIMARY KEY,
    de_usuario_id   INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    para_usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    contenido       TEXT NOT NULL,
    leido           BOOLEAN NOT NULL DEFAULT FALSE,
    enviado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- =====================================================================
-- Mensajes del formulario de contacto público
-- =====================================================================
CREATE TABLE IF NOT EXISTS contactos (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(120) NOT NULL,
    email       VARCHAR(160) NOT NULL,
    mensaje     TEXT NOT NULL,
    enviado_en  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- =====================================================================
-- DATOS DE PRUEBA
-- Contraseña para todos: "123456"
-- =====================================================================
INSERT INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel) VALUES
('Juan Martínez', 'entrenador@gymfit.com', crypt('123456', gen_salt('bf')), 'entrenador', 32, 'Formar campeones', 'Profesional'),
('Juan Pérez',    'juanperez@gmail.com',  crypt('123456', gen_salt('bf')), 'cliente',    28, 'Ganar masa muscular', 'Intermedio'),
('Ana Gómez',     'anagomez@gmail.com',   crypt('123456', gen_salt('bf')), 'cliente',    25, 'Perder grasa',        'Principiante'),
('Carlos Rodríguez','carlosrod@gmail.com',crypt('123456', gen_salt('bf')), 'cliente',    34, 'Tonificar',           'Intermedio'),
('María López',   'marialopez@gmail.com', crypt('123456', gen_salt('bf')), 'cliente',    29, 'Resistencia',         'Avanzado')
ON CONFLICT (email) DO NOTHING;

-- Asignar todos los clientes al entrenador
INSERT INTO entrenador_cliente (entrenador_id, cliente_id)
SELECT
    (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
    u.id
FROM usuarios u
WHERE u.rol = 'cliente'
ON CONFLICT DO NOTHING;

-- Rutina de ejemplo para Juan Pérez
INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
SELECT
    (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
    (SELECT id FROM usuarios WHERE email='entrenador@gmail.com' OR email='entrenador@gymfit.com' LIMIT 1),
    E'Día 1 - Pecho y tríceps\n- Press banca 4x10\n- Aperturas con mancuernas 3x12\n- Fondos en paralelas 3x10\n- Extensión de tríceps 3x12\n\nDía 2 - Espalda y bíceps\n- Dominadas 4x8\n- Remo con barra 4x10\n- Curl de bíceps 3x12\n- Curl martillo 3x12',
    E'Recuerda mantener una buena técnica en todos los ejercicios.\nSube el peso progresivamente cada semana.\nDescansa 60-90 segundos entre series.'
WHERE NOT EXISTS (
    SELECT 1 FROM rutinas r
    JOIN usuarios u ON u.id = r.cliente_id
    WHERE u.email = 'juanperez@gmail.com'
);
