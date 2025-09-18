<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reminder->title }}</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 {{ $reminder->title }}</h1>
    </div>
    
    <div class="content">
        <h3>Details:</h3>
        <p>{{ $reminder->description }}</p>

        <h3>Event Date:</h3>
        <p>{{ date('Y-m-d H:i:s', $reminder->event_at) }}</p>
    </div>
    
    <div class="footer">
        <p>This is an automated reminder from your reminder system.</p>
    </div>
</body>
</html>