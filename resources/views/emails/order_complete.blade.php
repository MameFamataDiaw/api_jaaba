<!DOCTYPE html>
<html>
<head>
    <title>Order Complete</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        h1 {
            color: #067470FF;
        }
        p {
            margin: 0 0 10px;
        }
        ul {
            margin: 0 0 20px;
            padding: 0;
            list-style: none;
        }
        li {
            margin: 0 0 5px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<h1> Etat de votre Commande </h1>
<p>Cher {{ $order->customer->prenom }},</p>
<p>Merci pour votre commande. Votre commande avec référence <strong>{{ $order->reference }}</strong> a été complété.</p>

<p><strong>Total:</strong> {{ $order->montant }} FCFA</p>

<p>Nous sommes heureux de vous informer que votre commande est désormais terminée. Un livreur vous contactera sous peu pour organiser la livraison de votre commande.</p>
<p>Si vous avez des questions ou si vous avez besoin d'aide, n'hésitez pas à nous contacter.</p>

<div class="footer">
<p>Merci d'avoir effectué vos achats chez nous !</p>
<p>Cordialement,<br>Votre nom d'entreprise</p>
</div>
</body>
</html>
