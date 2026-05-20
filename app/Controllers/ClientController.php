<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Auth;
use App\Helpers\Validator;
use App\Models\TrainerClient;
use App\Models\User;
use App\Security\Audit;

class ClientController
{
    public function list(): void
    {
        $trainer = Auth::require('entrenador');
        $clients = TrainerClient::getClients((int)$trainer['id']);
        Response::success(['clientes' => $clients]);
    }

    public function assign(): void
    {
        $trainer = Auth::require('entrenador');
        $input = Response::input();
        $email = trim($input['email'] ?? '');

        $v = new Validator();
        $v->required('email', $email, 'Email');

        if (!$v->passes()) {
            Response::error($v->firstError(), 400);
        }

        $client = User::findByEmail($email);

        if (!$client || $client['rol'] !== 'cliente') {
            Response::error('No existe un cliente con ese email', 404);
        }

        $alreadyAssigned = TrainerClient::findByEmail((int)$trainer['id'], $email);
        if ($alreadyAssigned === null) {
            $check = TrainerClient::getClients((int)$trainer['id']);
            foreach ($check as $c) {
                if ($c['email'] === $email) {
                    Response::error('El cliente ya está asignado a este entrenador', 409);
                }
            }
        }

        TrainerClient::assign((int)$trainer['id'], (int)$client['id']);
        Audit::log('CLIENTE_ASIGNADO', ['cliente' => $email], (int)$trainer['id']);

        Response::success(['message' => 'Cliente asignado correctamente']);
    }
}
