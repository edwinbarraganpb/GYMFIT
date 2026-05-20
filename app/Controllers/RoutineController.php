<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Auth;
use App\Helpers\Validator;
use App\Models\Routine;
use App\Security\Audit;

class RoutineController
{
    public function get(): void
    {
        $user = Auth::require();
        $cid = (int)($_GET['cliente_id'] ?? 0);

        if ($user['rol'] === 'cliente') {
            $cid = (int)$user['id'];
        }

        if ($cid <= 0) {
            Response::error('cliente_id requerido', 400);
        }

        $routine = Routine::getLatest($cid);
        Response::success(['rutina' => $routine]);
    }

    public function save(): void
    {
        $trainer = Auth::require('entrenador');
        $input = Response::input();

        $cid = (int)($input['cliente_id'] ?? 0);
        $contenido = trim($input['contenido'] ?? '');
        $observaciones = trim($input['observaciones'] ?? '');

        $v = new Validator();
        $v->required('cliente_id', $cid, 'Cliente')
          ->integer('cliente_id', $cid, 'Cliente')
          ->required('contenido', $contenido, 'Contenido');

        if (!$v->passes()) {
            Response::error($v->firstError(), 400);
        }

        $id = Routine::create([
            'cliente_id'    => $cid,
            'entrenador_id' => (int)$trainer['id'],
            'contenido'     => $contenido,
            'observaciones' => $observaciones,
        ]);

        Audit::log('RUTINA_ASIGNADA', ['cliente_id' => $cid, 'rutina_id' => $id], (int)$trainer['id']);

        Response::success(['id' => $id]);
    }
}
