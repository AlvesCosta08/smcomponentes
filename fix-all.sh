#!/bin/bash
# fix-all.sh

echo "🔧 CORREÇÃO COMPLETA DO PROJETO"
echo "================================"

# 1. Criar backup
echo "📦 Criando backup dos arquivos..."
cp app/Providers/AppServiceProvider.php app/Providers/AppServiceProvider.php.backup 2>/dev/null
cp app/Providers/ServiceBindingProvider.php app/Providers/ServiceBindingProvider.php.backup 2>/dev/null

# 2. Copiar os arquivos corrigidos
echo "📝 Atualizando AppServiceProvider..."
cat > app/Providers/AppServiceProvider.php << 'EOF'
<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!app()->isProduction()) {
            Log::info('🚀 AppServiceProvider::register() EXECUTADO!');
        }
    }

    public function boot(): void
    {
        if (!app()->isProduction()) {
            Log::info('🚀 AppServiceProvider::boot() EXECUTADO!');
        }

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
        $this->registerMacros();
        $this->configureSpatiePermission();
        date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));
    }

    protected function registerMacros(): void
    {
        if (!Str::hasMacro('currency')) {
            Str::macro('currency', fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.'));
        }

        if (!Str::hasMacro('price')) {
            Str::macro('price', fn($value) => 'R$ ' . number_format((float) $value, 2, ',', '.'));
        }

        if (!Str::hasMacro('cpf')) {
            Str::macro('cpf', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 11) {
                    return substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.' . 
                           substr($value, 6, 3) . '-' . substr($value, 9, 2);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('phone')) {
            Str::macro('phone', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 11) {
                    return '(' . substr($value, 0, 2) . ') ' . substr($value, 2, 5) . '-' . substr($value, 7, 4);
                }
                if (strlen($value) === 10) {
                    return '(' . substr($value, 0, 2) . ') ' . substr($value, 2, 4) . '-' . substr($value, 6, 4);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('cep')) {
            Str::macro('cep', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 8) {
                    return substr($value, 0, 5) . '-' . substr($value, 5, 3);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('data')) {
            Str::macro('data', fn($value) => $value ? Carbon::parse($value)->format('d/m/Y') : '');
        }

        if (!Str::hasMacro('datahora')) {
            Str::macro('datahora', fn($value) => $value ? Carbon::parse($value)->format('d/m/Y H:i') : '');
        }

        if (!Str::hasMacro('slug')) {
            Str::macro('slug', function ($text) {
                $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
                $text = strtolower(trim($text));
                $text = preg_replace('/\s+/', '-', $text);
                return $text;
            });
        }

        if (!Str::hasMacro('truncate')) {
            Str::macro('truncate', function ($text, $length = 100, $end = '...') {
                if (strlen($text) <= $length) return $text;
                return substr($text, 0, $length) . $end;
            });
        }

        if (!Str::hasMacro('statusPedido')) {
            Str::macro('statusPedido', function ($status) {
                $statuses = [
                    'pendente' => ['label' => 'Pendente', 'badge' => 'warning'],
                    'pago' => ['label' => 'Pago', 'badge' => 'info'],
                    'processando' => ['label' => 'Processando', 'badge' => 'primary'],
                    'enviado' => ['label' => 'Enviado', 'badge' => 'success'],
                    'entregue' => ['label' => 'Entregue', 'badge' => 'success'],
                    'cancelado' => ['label' => 'Cancelado', 'badge' => 'danger'],
                ];
                return $statuses[$status] ?? ['label' => ucfirst($status), 'badge' => 'secondary'];
            });
        }
    }

    protected function configureSpatiePermission(): void
    {
        try {
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                $this->app->make(\Spatie\Permission\PermissionRegistrar::class);
            }
        } catch (\Exception $e) {
            if (!app()->isProduction()) {
                Log::warning('Erro ao carregar PermissionRegistrar: ' . $e->getMessage());
            }
        }
    }
}
EOF

# 3. Verificar ServiceBindingProvider
echo "📝 Verificando ServiceBindingProvider..."
if [ ! -f "app/Providers/ServiceBindingProvider.php" ]; then
    echo "❌ ServiceBindingProvider.php não encontrado!"
    exit 1
fi

# 4. Limpar caches
echo "🧹 Limpando caches..."
docker exec -it smcomponentes_app bash -c "php artisan optimize:clear"
docker exec -it smcomponentes_app bash -c "php artisan config:clear"
docker exec -it smcomponentes_app bash -c "php artisan cache:clear"
docker exec -it smcomponentes_app bash -c "php artisan view:clear"
docker exec -it smcomponentes_app bash -c "php artisan route:clear"

# 5. Recarregar autoload
echo "📦 Recarregando autoload..."
docker exec -it smcomponentes_app bash -c "composer dump-autoload"

# 6. Recriar caches
echo "🔄 Recriando caches..."
docker exec -it smcomponentes_app bash -c "php artisan config:cache"
docker exec -it smcomponentes_app bash -c "php artisan route:cache"
docker exec -it smcomponentes_app bash -c "php artisan view:cache"

# 7. Testar rotas
echo "🧪 Testando rotas..."
docker exec -it smcomponentes_app bash -c "php artisan route:list | head -30"

# 8. Verificar logs
echo "📋 Últimos logs:"
docker exec -it smcomponentes_app tail -20 storage/logs/laravel.log

echo ""
echo "✅ CORREÇÃO CONCLUÍDA!"
echo "================================"
echo "🌐 Acesse: http://localhost:8080"
echo "👤 Admin: admin@smcomponentes.com"
echo "🔑 Senha: admin123"
