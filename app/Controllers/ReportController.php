<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Auth;
use App\Models\User;
use App\Models\Routine;
use App\Models\TrainerClient;

class ReportController
{
    public function clientProgress(): void
    {
        Auth::require('entrenador');

        $routinesByMonth = Routine::countByMonth();
        $routinesByClient = Routine::countByClient();
        $totalRoutines = Routine::total();

        Response::success([
            'rutinas_por_mes'   => $routinesByMonth,
            'rutinas_por_cliente' => $routinesByClient,
            'total_rutinas'     => $totalRoutines,
        ]);
    }

    public function gymStatistics(): void
    {
        $trainers = User::countByRol('entrenador');
        $clients = User::countByRol('cliente');
        $usersByMonth = User::countByMonth();
        $usersByGoal = User::countByObjetivo();
        $usersByLevel = User::countByNivel();
        $routinesByMonth = Routine::countByMonth();
        $clientsPerTrainer = TrainerClient::countClientsPerTrainer();

        Response::success([
            'total_entrenadores'  => $trainers,
            'total_clientes'      => $clients,
            'usuarios_por_mes'    => $usersByMonth,
            'usuarios_por_objetivo' => $usersByGoal,
            'usuarios_por_nivel'  => $usersByLevel,
            'rutinas_por_mes'     => $routinesByMonth,
            'clientes_por_entrenador' => $clientsPerTrainer,
        ]);
    }
}
