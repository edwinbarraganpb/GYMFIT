<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Models\Contact;
use App\Security\Audit;

class ContactController
{
    public function submit(): void
    {
        $input = Response::input();
        $nombre = trim($input['nombre'] ?? '');
        $email  = trim($input['email'] ?? '');
        $mensaje = trim($input['mensaje'] ?? '');

        $v = new Validator();
        $v->required('nombre', $nombre, 'Nombre')
          ->required('email', $email, 'Email')
          ->email('email', $email, 'Email')
          ->required('mensaje', $mensaje, 'Mensaje');

        if (!$v->passes()) {
            Response::error($v->firstError(), 400);
        }

        Contact::create([
            'nombre'  => $nombre,
            'email'   => $email,
            'mensaje' => $mensaje,
        ]);

        Audit::log('CONTACTO', ['email' => $email]);

        Response::success(['message' => 'Mensaje enviado correctamente']);
    }
}
