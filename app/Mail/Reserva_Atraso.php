<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Reserva_Atraso extends Mailable
{
    use Queueable, SerializesModels;

    public $topic;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reserva)
    {
        $this->topic = $reserva;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reserva_Atraso');
    }
}
