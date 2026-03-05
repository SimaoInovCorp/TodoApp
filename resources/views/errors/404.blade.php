<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7fafc;
            color: #222;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        a.button {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #2d3748;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        a.button:hover {
            background: #4a5568;
        }
        .emoji {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="emoji">🌲🧭🌲</div>
        <h1>404</h1>
        <p>Woops, guess you got lost in the woods!</p>
        <a href="/tasks" class="button">Back to Tasks</a>
    </div>
</body>
</html>
