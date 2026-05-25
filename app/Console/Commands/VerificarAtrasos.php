<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserve;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
// Lembra-te que terás de criar esta notificação a seguir!
use App\Notifications\AvisoAtraso; 

class VerificarAtrasos extends Command
{
    // O nome que vais usar no terminal para chamar este comando
    protected $signature = 'reservas:verificar-atrasos';

    // Uma breve descrição para saberes o que faz
    protected $description = 'Verifica reservas que já passaram a data de fim e ainda não foram devolvidas.';

    public function __construct()
    {
        parent::__construct();
    }

   public function handle()
{
    $hoje = Carbon::today();

    Reserve::whereNull('return_date')
        ->whereDate('end_date', '<', $hoje)
        ->where('reserve_state_id', '!=', 3)
        ->update(['reserve_state_id' => 4]);

        $reservasAtrasadas = Reserve::whereNull('return_date') 
        ->whereDate('end_date', '<', $hoje)                
        ->where('reserve_state_id', '!=', 3)               
        ->get();

    if ($reservasAtrasadas->isEmpty()) {
        $this->info('Tudo em dia! Não há reservas em atraso.');
        return;
    }

    $gestores = User::where('user_type_id', 1)->get();

    foreach ($reservasAtrasadas as $reserva) {
        if ($reserva->user) {
            $reserva->user->notify(new AvisoAtraso($reserva, 'requisitante'));
        }

        if ($gestores->isNotEmpty()) {
            Notification::send($gestores, new AvisoAtraso($reserva, 'gestor'));
        }
    }

    $this->info(count($reservasAtrasadas) . ' reservas em atraso atualizadas e notificadas com sucesso!');
}
}