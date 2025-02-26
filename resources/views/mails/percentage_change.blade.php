<!DOCTYPE html>
<html>
<head>
    <title>Percentage Alert</title>
</head>
<body>
    <p>Hello, {{ $user['email'] }} </p>

    <p>The current price has changed with more than {{ $user['percent_change'] }} in the last {{ $user['time_interval'] }}</p>
    <p>Percent change: {{ $historyPercentageChange }}</p>

    <p>Thank you for subscribing!</p>
</body>
</html>
