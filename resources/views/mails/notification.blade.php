<!DOCTYPE html>
<html>
<head>
    <title>{{ $mailType }} Alert</title>
</head>
<body>
    <p>Hello, {{ $subscriber->email }} </p>

    @if($mailType === 'percentage')
        <p>The current price has changed with more than {{ $subscriber->percent_change }} in the last {{ $subscriber->time_interval }}</p>
        <p>Percent change: {{ $additinalData }}</p>
    @elseif($mailType === 'price')
        <p>The current price has reached {{ $additinalData }}, which meets your alert target of {{ $subscriber['target_price'] }}.</p>
    @else
        <p>Unknown notification type.</p>
    @endif

    <p>Thank you for subscribing!</p>
</body>
</html>
