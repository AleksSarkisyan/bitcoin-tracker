<!DOCTYPE html>
<html>
<head>
    <title>Price Alert</title>
</head>
<body>
    <p>Hello, {{ $subscriber['email'] }} </p>

    <p>The current price has reached {{ $currentPrice }}, which meets your alert target of {{ $subscriber['target_price'] }}.</p>

    <p>Thank you for subscribing!</p>
</body>
</html>
