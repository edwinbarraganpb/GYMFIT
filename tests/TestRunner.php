<?php

declare(strict_types=1);

/**
 * GYMFIT - Test Agent / Test Runner
 *
 * Modo de uso:
 *   php tests/TestRunner.php                     # Ejecuta todos los tests
 *   php tests/TestRunner.php AuthTest            # Ejecuta un test específico
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Helpers\Database;
use App\Helpers\Auth;
use App\Models\User;
use App\Models\Routine;
use App\Models\Contact;
use App\Models\TrainerClient;

abstract class TestCase
{
    protected array $errors = [];
    protected int $assertions = 0;
    protected string $name;

    public function __construct()
    {
        $this->name = (new ReflectionClass($this))->getShortName();
    }

    abstract public function run(): void;

    protected function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            $this->errors[] = "FAIL: $message";
        }
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertTrue($expected === $actual, "$message — esperado: " . json_encode($expected) . ", obtenido: " . json_encode($actual));
    }

    protected function assertFalse(bool $condition, string $message): void
    {
        $this->assertions++;
        if ($condition) {
            $this->errors[] = "FAIL: $message";
        }
    }

    protected function assertNotNull(mixed $value, string $message): void
    {
        $this->assertTrue($value !== null, "$message — se esperaba un valor no nulo");
    }

    protected function assertNull(mixed $value, string $message): void
    {
        $this->assertTrue($value === null, "$message — se esperaba nulo");
    }

    public function report(): array
    {
        return [
            'name'       => $this->name,
            'assertions' => $this->assertions,
            'errors'     => $this->errors,
            'passed'     => count($this->errors) === 0,
        ];
    }
}

// ============================================================
// Auth Tests
// ============================================================

class AuthTest extends TestCase
{
    public function run(): void
    {
        // Test: password hash and verify
        $hash = Auth::hash('test123');
        $this->assertTrue(Auth::verify('test123', $hash), 'password_verify debería validar el hash correcto');
        $this->assertFalse(Auth::verify('wrong', $hash), 'password_verify debería rechazar contraseña incorrecta');

        // Test: find user by email
        $user = User::findByEmail('entrenador@gymfit.com');
        $this->assertNotNull($user, 'entrenador@gymfit.com debería existir');
        if ($user) {
            $this->assertEquals('Juan Martínez', $user['nombre'], 'Nombre del entrenador');
            $this->assertEquals('entrenador', $user['rol'], 'Rol del entrenador');
        }

        // Test: find non-existent user
        $none = User::findByEmail('noexiste@test.com');
        $this->assertNull($none, 'Usuario inexistente debería retornar null');

        // Test: duplicate email detection
        $dup = User::findByEmail('juanperez@gmail.com');
        $this->assertNotNull($dup, 'juanperez@gmail.com debería existir');
        if ($dup) {
            $this->assertEquals('cliente', $dup['rol'], 'Rol del cliente');
        }
    }
}

// ============================================================
// Client Tests
// ============================================================

class ClientTest extends TestCase
{
    public function run(): void
    {
        $trainer = User::findByEmail('entrenador@gymfit.com');
        $this->assertNotNull($trainer, 'Entrenador debería existir');
        if (!$trainer) return;

        $clients = TrainerClient::getClients((int)$trainer['id']);
        $this->assertTrue(count($clients) > 0, 'Entrenador debería tener clientes asignados');

        $clientFound = false;
        foreach ($clients as $c) {
            if ($c['email'] === 'juanperez@gmail.com') {
                $clientFound = true;
                break;
            }
        }
        $this->assertTrue($clientFound, 'Juan Pérez debería estar en la lista de clientes del entrenador');
    }
}

// ============================================================
// Routine Tests
// ============================================================

class RoutineTest extends TestCase
{
    public function run(): void
    {
        $client = User::findByEmail('juanperez@gmail.com');
        $this->assertNotNull($client, 'Cliente debería existir');
        if (!$client) return;

        $routine = Routine::getLatest((int)$client['id']);
        $this->assertNotNull($routine, 'Juan Pérez debería tener una rutina asignada');
        if ($routine) {
            $this->assertTrue(strlen($routine['contenido']) > 0, 'La rutina debería tener contenido');
            $this->assertNotNull($routine['entrenador_nombre'], 'La rutina debería tener nombre del entrenador');
        }

        $total = Routine::total();
        $this->assertTrue($total > 0, 'Debería haber al menos 1 rutina en la BD');
    }
}

// ============================================================
// Contact Tests
// ============================================================

class ContactTest extends TestCase
{
    public function run(): void
    {
        $id = Contact::create([
            'nombre' => 'Test Contact',
            'email'  => 'test@contact.com',
            'mensaje' => 'Este es un mensaje de prueba',
        ]);
        $this->assertTrue($id > 0, 'Debería crear un contacto y retornar ID > 0');

        $all = Contact::all();
        $this->assertTrue(count($all) > 0, 'Debería haber al menos 1 contacto');
    }
}

// ============================================================
// Runner
// ============================================================

echo "\n" . str_repeat('=', 60) . "\n";
echo "  GYMFIT - Test Agent / Pruebas Unitarias y Regresión\n";
echo str_repeat('=', 60) . "\n\n";

$specific = $argv[1] ?? null;
$testClasses = [AuthTest::class, ClientTest::class, RoutineTest::class, ContactTest::class];
$results = [];
$passed = 0;
$failed = 0;
$totalAssertions = 0;

foreach ($testClasses as $class) {
    $shortName = (new ReflectionClass($class))->getShortName();
    if ($specific && stripos($shortName, $specific) === false) {
        continue;
    }

    /** @var TestCase $test */
    $test = new $class();

    try {
        $test->run();
    } catch (\Throwable $e) {
        echo "  ! {$shortName}: ERROR - " . $e->getMessage() . "\n";
        continue;
    }

    $report = $test->report();
    $results[] = $report;
    $totalAssertions += $report['assertions'];

    $icon = $report['passed'] ? '✓' : '✗';
    echo "  {$icon} {$shortName}: {$report['assertions']} aserciones";
    if ($report['passed']) {
        echo " - OK\n";
        $passed++;
    } else {
        echo " - FALLÓ\n";
        $failed++;
        foreach ($report['errors'] as $err) {
            echo "       {$err}\n";
        }
    }
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "  Resumen: " . ($passed + $failed) . " tests, {$totalAssertions} aserciones\n";
echo "  Pasaron: {$passed}  Fallaron: {$failed}\n";
echo str_repeat('=', 60) . "\n\n";

exit($failed > 0 ? 1 : 0);
