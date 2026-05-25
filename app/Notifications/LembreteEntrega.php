<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserve;

class LembreteEntrega extends Notification
{
    use Queueable;

    public $reserve;

    public function __construct(Reserve $reserve)
    {
        $this->reserve = $reserve;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Lembrete: Entrega de material amanhã')
                    ->greeting('Olá, ' . $this->reserve->user->name . '!')
                    ->line('Este é um lembrete automático de que o prazo da tua requisição de material termina amanhã.')
                    ->line('Detalhes do Material: ' . $this->reserve->description)
                    ->line('Data limite de entrega: ' . date('d/m/Y', strtotime($this->reserve->end_date)))
                    ->line('Por favor, garante que passas no LIA amanhã para efetuar a devolução dentro do horário previsto.')
                    ->line('Obrigado pela cooperação!');
    }
}