<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../../database/gymfit.db';
            $dir = dirname($dbPath);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fresh = !file_exists($dbPath);

            try {
                self::$instance = new PDO('sqlite:' . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                self::$instance->exec('PRAGMA journal_mode=WAL');
                self::$instance->exec('PRAGMA foreign_keys=ON');

                if ($fresh) {
                    self::initSchema();
                    self::seedData();
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
                exit;
            }
        }

        return self::$instance;
    }

    private static function initSchema(): void
    {
        $pdo = self::$instance;

        $pdo->exec("
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
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS entrenador_cliente (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                creado_en       TEXT NOT NULL DEFAULT (datetime('now')),
                UNIQUE(entrenador_id, cliente_id)
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rutinas (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                contenido       TEXT NOT NULL,
                observaciones   TEXT,
                asignada_en     TEXT NOT NULL DEFAULT (datetime('now')),
                actualizada_en  TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS mensajes (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                de_usuario_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                para_usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                contenido       TEXT NOT NULL,
                leido           INTEGER NOT NULL DEFAULT 0,
                enviado_en      TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contactos (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre      TEXT NOT NULL,
                email       TEXT NOT NULL,
                mensaje     TEXT NOT NULL,
                enviado_en  TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS auditoria (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id  INTEGER,
                accion      TEXT NOT NULL,
                detalle     TEXT,
                ip          TEXT,
                user_agent  TEXT,
                creado_en   TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");
    }

    private static function seedData(): void
    {
        $pdo = self::$instance;
        $hash = password_hash('123456', PASSWORD_BCRYPT);

        $ins = $pdo->prepare(
            "INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $ins->execute(['Juan Martínez', 'entrenador@gymfit.com', $hash, 'entrenador', 32, 'Formar campeones', 'Profesional']);
        $ins->execute(['Juan Pérez', 'juanperez@gmail.com', $hash, 'cliente', 28, 'Ganar masa muscular', 'Intermedio']);
        $ins->execute(['Ana Gómez', 'anagomez@gmail.com', $hash, 'cliente', 25, 'Perder grasa', 'Principiante']);
        $ins->execute(['Carlos Rodríguez', 'carlosrod@gmail.com', $hash, 'cliente', 34, 'Tonificar', 'Intermedio']);
        $ins->execute(['María López', 'marialopez@gmail.com', $hash, 'cliente', 29, 'Resistencia', 'Avanzado']);
        $ins->execute(['Pedro Sánchez', 'pedro@gmail.com', $hash, 'cliente', 31, 'Ganar masa muscular', 'Principiante']);
        $ins->execute(['Laura Díaz', 'laura@gmail.com', $hash, 'cliente', 27, 'Perder grasa', 'Intermedio']);
        $ins->execute(['Roberto Ruiz', 'roberto@gmail.com', $hash, 'cliente', 35, 'Tonificar', 'Avanzado']);

        $pdo->exec("
            INSERT OR IGNORE INTO entrenador_cliente (entrenador_id, cliente_id)
            SELECT (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'), u.id
            FROM usuarios u WHERE u.rol = 'cliente'
        ");

        $pdo->exec("
            INSERT OR IGNORE INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
            SELECT
                (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
                (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
                'Día 1 - Pecho y tríceps' || CHAR(10) || CHAR(10) ||
                '- Press banca 4x10' || CHAR(10) ||
                '- Aperturas con mancuernas 3x12' || CHAR(10) ||
                '- Fondos en paralelas 3x10' || CHAR(10) ||
                '- Extensión de tríceps 3x12' || CHAR(10) || CHAR(10) ||
                'Día 2 - Espalda y bíceps' || CHAR(10) || CHAR(10) ||
                '- Dominadas 4x8' || CHAR(10) ||
                '- Remo con barra 4x10' || CHAR(10) ||
                '- Curl de bíceps 3x12' || CHAR(10) ||
                '- Curl martillo 3x12',
                'Recuerda mantener una buena técnica en todos los ejercicios.' || CHAR(10) ||
                'Sube el peso progresivamente cada semana.' || CHAR(10) ||
                'Descansa 60-90 segundos entre series.'
        ");

        $pdo->exec("
            INSERT OR IGNORE INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
            SELECT
                (SELECT id FROM usuarios WHERE email='anagomez@gmail.com'),
                (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
                'Día 1 - Full body' || CHAR(10) || CHAR(10) ||
                '- Sentadillas 3x12' || CHAR(10) ||
                '- Press militar 3x10' || CHAR(10) ||
                '- Remo con mancuerna 3x10' || CHAR(10) ||
                '- Plancha 3x30s' || CHAR(10) || CHAR(10) ||
                'Día 2 - Cardio' || CHAR(10) || CHAR(10) ||
                '- Cinta 20 min' || CHAR(10) ||
                '- Bicicleta 15 min' || CHAR(10) ||
                '- Elíptica 10 min',
                'Comienza con poco peso y enfócate en la técnica.' || CHAR(10) ||
                'Hidrátate bien antes y después del entrenamiento.'
        ");

        $pdo->exec("
            INSERT OR IGNORE INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
            SELECT
                (SELECT id FROM usuarios WHERE email='carlosrod@gmail.com'),
                (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
                'Día 1 - Push' || CHAR(10) || CHAR(10) ||
                '- Press banca inclinado 4x10' || CHAR(10) ||
                '- Press francés 3x12' || CHAR(10) ||
                '- Elevaciones laterales 3x15' || CHAR(10) || CHAR(10) ||
                'Día 2 - Pull' || CHAR(10) || CHAR(10) ||
                '- Dominadas 4x8' || CHAR(10) ||
                '- Remo en T 4x10' || CHAR(10) ||
                '- Curl alternado 3x12',
                'Controla el movimiento en todas las fases.' || CHAR(10) ||
                'Aumenta peso cuando completes todas las repeticiones con buena forma.'
        ");
    }
}
