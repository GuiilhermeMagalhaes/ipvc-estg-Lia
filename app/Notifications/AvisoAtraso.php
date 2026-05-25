<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserve;

class AvisoAtraso extends Notification
{
    use Queueable;

    public $reserve;
    public $tipo; // Variável nova para sabermos quem vai receber o email

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reserve $reserve, $tipo)
    {
        $this->reserve = $reserve;
        $this->tipo = $tipo; // Guardamos o tipo ('requisitante' ou 'gestor')
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Se o email for para o Gestor do LIA
        if ($this->tipo == 'gestor') {
            return (new MailMessage)
                        ->subject('Aviso: Material em Atraso')
                        ->greeting('Olá, Gestor!')
                        ->line('O sistema detetou que existe material em atraso que ainda não foi devolvido.')
                        ->line('Requisitante: ' . $this->reserve->user->name)
                        ->line('Detalhes da Reserva: ' . $this->reserve->description)
                        ->action('Ver Reserva', url('/requisicoes/' . $this->reserve->id));
        }

        // Se o email for para o Aluno/Requisitante
        return (new MailMessage)
                    ->subject('Aviso de Atraso: Devolução de Material')
                    ->greeting('Olá, ' . $this->reserve->user->name . '!')
                    ->line('O prazo para a devolução do equipamento da tua reserva já terminou.')
                    ->line('Detalhes da Reserva: ' . $this->reserve->description)
                    ->line('Por favor, dirige-te ao LIA para efetuares a devolução do material o mais rapidamente possível.')
                    ->action('Ver Minhas Reservas', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}