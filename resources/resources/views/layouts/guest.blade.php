<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SM Componentes') }} - @yield('title', 'Autenticação')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-dark: #0f172a;
            --primary: #2563eb;
            --secondary: #f97316;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --shadow: 0 4px 6px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.1);
            --radius: 12px;
            --radius-full: 50px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1a2a4a 50%, var(--gray-800) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: white;
            border-radius: var(--radius);
            padding: 40px 30px;
            box-shadow: var(--shadow-lg);
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-brand .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .auth-brand .logo i {
            font-size: 2.2rem;
            color: var(--secondary);
        }

        .auth-brand .logo span {
            color: var(--primary);
        }

        .auth-brand p {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .auth-card .form-control {
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .auth-card .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .auth-card .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.9rem;
        }

        .auth-card .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: var(--radius-full);
            transition: all 0.3s ease;
        }

        .auth-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.3);
        }

        .auth-card .btn-secondary-custom {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-light));
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: var(--radius-full);
            color: white;
            transition: all 0.3s ease;
        }

        .auth-card .btn-secondary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(249, 115, 22, 0.3);
            color: white;
        }

        .auth-card .auth-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-card .auth-link:hover {
            color: var(--secondary);
        }

        .auth-card .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: var(--gray-400);
            font-size: 0.85rem;
        }

        .auth-card .divider::before,
        .auth-card .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid var(--gray-200);
        }

        .auth-card .divider::before {
            margin-right: 15px;
        }

        .auth-card .divider::after {
            margin-left: 15px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
        }

        .auth-footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            color: white;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 30px 20px;
            }
            .auth-brand .logo {
                font-size: 1.4rem;
            }
            .auth-brand .logo i {
                font-size: 1.8rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <a href="{{ route('home') }}" class="logo">
                    <i class="bi bi-plug"></i>
                    <span>SM</span> Componentes
                </a>
                <p>@yield('subtitle', 'Acesse sua conta')</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot }}
        </div>

        <div class="auth-footer">
            &copy; {{ date('Y') }} SM Componentes. Todos os direitos reservados.
            <br>
            <a href="{{ route('home') }}"><i class="bi bi-house"></i> Voltar para a loja</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html
