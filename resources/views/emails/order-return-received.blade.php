<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Demande de Retour</title>
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
    <h1>Confirmation de Demande de Retour</h1>
</div>
<div class="content">
    <p>Cher {{ $order_return->customer ? $order_return->customer->prenom : 'Client' }},</p>
    <p>Nous avons bien reçu votre demande de retour pour la commande <strong>{{ $order_return->reference }}</strong>.</p>
    <p>Voici les détails de votre demande :</p>
    <ul>
        <li><strong>Motif du retour :</strong> {{ $order_return->motif }}</li>
        <li><strong>Montant de la commande :</strong> {{ number_format($order_return->montant, 0, ',', ' ') }} FCFA</li>
        <li><strong>Date de la commande :</strong> {{ $order_return->created_at->format('d/m/Y') }}</li>
        <li><strong>Date de la demande de retour :</strong> {{ $order_return->created_at->format('d/m/Y') }}</li>
    </ul>
    <p>Votre demande est actuellement <strong>en cours de traitement</strong>. Nous vous tiendrons informé(e) de la suite dès que possible.</p>
    <p>Vous pouvez consulter l'état de votre retour en vous connectant à votre compte sur notre site.</p>
</div>
<div class="footer">
    <p>Merci d'avoir choisi notre Boutique. Nous restons à votre disposition pour toute question.</p>
</div>
</body>
</html>
