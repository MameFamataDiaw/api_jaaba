<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #594CAFFF;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #4C56AFFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Merci pour votre commande !</h1>
</div>
<div class="content">
    <p>Cher {{ $order->customer ? $order->customer->prenom : 'Client' }},</p>
    <p>Votre Commande <strong>{{ $order->reference }}</strong> a été confirmé avec succès.</p>
    <p>Details Commande:</p>
    <ul>
        <li> Montant Total: {{ number_format($order->montant, 0, ',', ' ') }} FCFA</li>
        <li>Date Commande: {{ $order->created_at->format('m/d/Y') }}</li>
    </ul>
    <p>Pour suivre votre commande, visitez notre site, cliquez sur « Suivre la commande », saisissez votre référence de commande et cliquez sur « Suivre ».</p>
    <p>Nous vous informerons dès que votre commande sera expédiée.</p>
</div>
<div class="footer">
    <p>Merci d'avoir choisi notre Boutique !</p>
</div>
</body>
</html>
