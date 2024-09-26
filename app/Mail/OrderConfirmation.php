<?php

namespace App\Mail;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Commande $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->view('emails.order-confirmation')
            ->subject('Confirmation de votre commande');
    }
}
