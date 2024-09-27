<?php

namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandeNotification extends Notification
{
    use Queueable;

    private $commande;
    private $statut;

    /**
     * Create a new notification instance.
     */
    public function __construct(Commande $commande, $statut)
    {
        $this->commande = $commande;
        $this->statut = $statut;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Commande numero')
                    ->line('Le statut de votre commande a ete mis a jour.')
                    ->line('Statut actuel : ' . $this->statut)
                    ->line('Montant total de la commande : ' . $this->commande->montant . ' FCFA')
                    ->line('Merci d\'avoir commandé sur notre plateforme !');
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'commande_id' => $this->commande->id,
            'statut' => $this->statut,
        ];
    }
}
