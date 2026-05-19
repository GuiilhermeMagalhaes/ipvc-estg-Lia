<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserve;

class PedidoRequisicao extends Notification
{
    use Queueable;

    // 1. Declarar a variável aqui no topo!
    public $reserve;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reserve $reserve)
    {
        $this->reserve = $reserve;
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
        return (new MailMessage)
                    ->subject('Nova Requisição Pendente de Aprovação')
                    ->greeting('Olá, Gestor!')
                    ->line('Existe um novo pedido de requisição pendente no sistema.')
                    ->line('Requisitante: ' . $this->reserve->user->name)
                    // Atenção: Verifica se o teu Model Reserve tem 'description' ou 'descricao'. 
                    // No teu Controller usavas 'description', por isso mudei aqui:
                    ->line('Detalhes do Pedido: ' . $this->reserve->description)
                    ->action('Analisar Requisição', url('/requisicoes/' . $this->reserve->id))
                    ->line('Por favor, aceda à plataforma para aprovar ou rejeitar o pedido.');
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