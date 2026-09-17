<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/images/welcome-bg.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            text-align: center;
            color: #fff;
            padding: 3rem;
            max-width: 600px;
        }
        .welcome-card h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .welcome-card p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .welcome-card .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
            margin: 0 0.5rem;
        }
        .btn-primary {
            background: #0d6efd;
            color: #fff;
        }
        .btn-primary:hover { background: #0b5ed7; }
        .btn-outline {
            border: 2px solid #fff;
            color: #fff;
        }
        .btn-outline:hover { background: rgba(255,255,255,0.15); }
    </style>
</head>
<body>
    <div class="welcome-card">
        <h1>{{ config('app.name', 'Quick HR') }}</h1>
        <p>Streamline your HR operations with our comprehensive management platform.</p>
        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/home') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
                @endauth
            @endif
        </div>
    </div>
</body>
</html>
