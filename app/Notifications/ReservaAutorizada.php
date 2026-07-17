<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserve;

class ReservaAutorizada extends Notification
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
                    ->subject('A sua requisição foi autorizada')
                    ->greeting('Olá, ' . $this->reserve->user->name . '!')
                    ->line('A sua requisição foi autorizada.')
                    ->line('Descrição: ' . $this->reserve->description)
                    ->line('Data de início: ' . \Carbon\Carbon::parse($this->reserve->start_date)->format('d/m/Y'))
                    ->line('Data de fim: ' . \Carbon\Carbon::parse($this->reserve->end_date)->format('d/m/Y'))
                    ->action('Ver as Minhas Reservas', config('app.url') . '/perfil/reserves')
                    ->line('Pode agora combinar o levantamento do equipamento com o técnico do laboratório, dentro das datas indicadas.');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}   