<?php



namespace App\Http\Middleware;

use Closure;
use App\Reserva;
use App\User;
use Illuminate\Support\Facades\Mail;
use \App\Mail\Reserva_Atraso;
use \App\Mail\NovosAtrasosToFuncionarios;





class CheckDates
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
    }
}
