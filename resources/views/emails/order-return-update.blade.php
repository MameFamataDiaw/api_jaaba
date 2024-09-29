<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traitement de la Demande de Retour</title>
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
    <h1>{{ $order_return->statut === 'acceptée' ? 'Retour Accepté' : 'Retour Refusé' }}</h1>
</div>
<div class="content">
    <p>Cher {{ $retour->customer ? $retour->customer->prenom : 'Client' }},</p>

    @if ($retour->statut === 'acceptée')
        <p>Nous sommes heureux de vous informer que votre demande de retour pour la commande <strong>{{ $retour->reference }}</strong> a été <strong>acceptée</strong>.</p>
        <p>Les produits retournés seront remis en stock, et nous vous contacterons prochainement pour le remboursement ou tout autre processus nécessaire.</p>
        <p>Montant Total de la commande : <strong>{{ number_format($retour->montant, 0, ',', ' ') }} FCFA</strong></p>
        <p>Date de la commande : <strong>{{ $order_return->created_at->format('d/m/Y') }}</strong></p>
        <p>Date de la demande de retour : <strong>{{ $order_return->created_at->format('d/m/Y') }}</strong></p>
    @else
        <p>Nous vous informons que votre demande de retour pour la commande <strong>{{ $retour->reference }}</strong> a été <strong>refusée</strong>.</p>
        <p>Motif du refus : {{ $order_return->motif }}</p>
        <p>Si vous avez des questions ou souhaitez obtenir plus de détails, n’hésitez pas à nous contacter.</p>
    @endif

    <p>Pour suivre vos commandes ou contacter notre support, veuillez vous connecter à votre compte sur notre site.</p>
</div>
<div class="footer">
    <p>Merci d'avoir choisi notre Boutique. Nous restons à votre disposition pour toute question.</p>
</div>
</body>
</html>

