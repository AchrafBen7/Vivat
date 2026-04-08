<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StripeDisputeAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly object $charge) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('[URGENT] Litige Stripe détecté — ' . ($this->charge->id ?? 'inconnu'))
            ->line('Un litige (chargeback) a été ouvert sur un paiement Stripe.')
            ->line('**Charge ID :** ' . ($this->charge->id ?? 'N/A'))
            ->line('**Montant :** ' . number_format(($this->charge->amount ?? 0) / 100, 2) . ' ' . strtoupper($this->charge->currency ?? 'eur'))
            ->line('Connectez-vous au dashboard Stripe pour gérer ce litige immédiatement.')
            ->action('Ouvrir Stripe Dashboard', 'https://dashboard.stripe.com/disputes');
    }
}
