<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NovosAtrasosToFuncionarios extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $userReserva;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reserva,$userReserva)
    {
        $this->reserva = $reserva;
        $this->userReserva = $userReserva;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reserva_AtrasoFuncionarios');
    }
}
