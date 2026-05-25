<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserve;
use Carbon\Carbon;
use App\Notifications\LembreteEntrega;

class EnviarLembretesEntrega extends Command
{
    protected $signature = 'reservas:lembrete-entrega';

    protected $description = 'Envia um email de lembrete aos utilizadores que têm de entregar material amanhã.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $amanha = Carbon::tomorrow();

        $reservasParaAmanha = Reserve::whereNull('return_date')
            ->whereDate('end_date', '=', $amanha)
            ->where('reserve_state_id', '!=', 3) // Ignorar canceladas
            ->get();

        if ($reservasParaAmanha->isEmpty()) {
            $this->info('Nenhuma reserva termina amanhã. Sem emails para enviar.');
            return;
        }

        foreach ($reservasParaAmanha as $reserva) {
            if ($reserva->user) {
                $reserva->user->notify(new LembreteEntrega($reserva));
            }
        }

        $this->info(count($reservasParaAmanha) . ' lembretes de véspera enviados com sucesso!');
    }
}