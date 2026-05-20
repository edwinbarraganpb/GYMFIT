<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Auth;
use App\Helpers\Validator;
use App\Models\User;
use App\Security\Audit;

class AuthController
{
    public function login(): void
    {
        $input = Response::input();
        $email = trim($input['email'] ?? '');
        $pass  = (string)($input['password'] ?? '');
        $rol   = $input['rol'] ?? null;

        $v = new Validator();
        $v->required('email', $email, 'Email')
          ->required('password', $pass, 'Contraseña');

        if (!$v->passes()) {
            Response::error($v->firstError(), 400);
        }

        $user = User::findByEmail($email);

        if (!$user || !Auth::verify($pass, $user['password_hash'])) {
            Audit::log('LOGIN_FALLIDO', ['email' => $email]);
            Response::error('Credenciales inválidas', 401);
        }

        if ($rol && $user['rol'] !== $rol) {
            Response::error('Este usuario no tiene el rol seleccionado', 403);
        }

        unset($user['password_hash']);
        Auth::login($user);
        Audit::log('LOGIN_EXITOSO', ['usuario' => $user['email']], (int)$user['id']);

        Response::success(['user' => $user]);
    }

    public function register(): void
    {
        $input = Response::input();
        $nombre = trim($input['nombre'] ?? '');
        $email  = trim($input['email'] ?? '');
        $pass   = (string)($input['password'] ?? '');
        $rol    = $input['rol'] ?? '';

        $v = new Validator();
        $v->required('nombre', $nombre, 'Nombre')
          ->required('email', $email, 'Email')
          ->email('email', $email, 'Email')
          ->required('password', $pass, 'Contraseña')
          ->minLength('password', $pass, 6, 'Contraseña')
          ->required('rol', $rol, 'Rol')
          ->inArray('rol', $rol, ['entrenador', 'cliente'], 'Rol');

        if (!$v->passes()) {
            Response::error($v->firstError(), 400);
        }

        $existing = User::findByEmail($email);
        if ($existing) {
            Response::error('Ese email ya está registrado', 409);
        }

        $user = User::create([
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => Auth::hash($pass),
            'rol'           => $rol,
        ]);

        Auth::login($user);
        Audit::log('REGISTRO', ['usuario' => $email], (int)$user['id']);

        Response::success(['user' => $user]);
    }

    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            Audit::log('LOGOUT', [], (int)$user['id']);
        }
        Auth::logout();
        Response::success();
    }

    public function me(): void
    {
        Response::success(['user' => Auth::user()]);
    }
}
