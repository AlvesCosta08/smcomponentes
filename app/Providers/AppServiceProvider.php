<?php

namespace App\Providers;

use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Infrastructure\Repositories\EloquentProdutoRepository;
use App\Interfaces\Storage\ImageUploaderInterface;
use App\Infrastructure\Storage\LocalImageUploader;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * DICA ARQUITETURA: É aqui que você registra as Interfaces 
     * apontando para suas Implementações Concretas (Inversão de Dependência).
     */
    public function register(): void
    {
        // 1. Bind do Upload de Imagem (Infraestrutura)
        $this->app->bind(
            ImageUploaderInterface::class,
            LocalImageUploader::class
        );

        // 2. Bind do Repositório de Produtos (Arquitetura Hexagonal)
        // A aplicação depende da Interface (Domínio), não da implementação concreta (Infraestrutura)
        $this->app->bind(
            ProdutoRepositoryInterface::class,
            EloquentProdutoRepository::class
        );

        // 3. Bind do Repositório de Pedidos (Preparado para o próximo módulo)
        // $this->app->bind(
        //     \App\Domain\Pedidos\Repositories\PedidoRepositoryInterface::class,
        //     \App\Infrastructure\Repositories\EloquentPedidoRepository::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forçar HTTPS em produção
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Paginação com Bootstrap 5
        Paginator::useBootstrapFive();

        // Registrar Macros de Apresentação (Blade)
        $this->registerMacros();
    }

    /**
     * Registrar macros úteis para a Camada de Apresentação (Views/Blade).
     * NOTA: Lógica de negócio (como cálculo de preços ou validação) NÃO deve ficar aqui.
     * Use os Value Objects (Money, Stock, Cpf, Cnpj) na camada de Domínio para validação.
     * As macros aqui são APENAS para formatação de saída (Presentation Layer).
     */
    protected function registerMacros(): void
    {
        // ==========================================
        // FORMATADORES MONETÁRIOS
        // ==========================================
        if (!Str::hasMacro('currency')) {
            Str::macro('currency', function (float|int|null $value): string {
                return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
            });
        }

        // ==========================================
        // FORMATADORES DE DOCUMENTOS (APENAS FORMATAÇÃO, NÃO VALIDAÇÃO)
        // ==========================================
        if (!Str::hasMacro('cpf')) {
            Str::macro('cpf', function (string|int|null $value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                return strlen($value) === 11 
                    ? substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.' . substr($value, 6, 3) . '-' . substr($value, 9, 2)
                    : (string) $value;
            });
        }

        if (!Str::hasMacro('cnpj')) {
            Str::macro('cnpj', function (string|int|null $value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                return strlen($value) === 14 
                    ? substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.' . substr($value, 5, 3) . '/' . substr($value, 8, 4) . '-' . substr($value, 12, 2)
                    : (string) $value;
            });
        }

        // NOVO: Formatador de Inscrição Estadual (Limpa caracteres não numéricos)
        // Nota: A formatação visual da IE varia por estado, então o padrão é manter apenas os números
        if (!Str::hasMacro('ie')) {
            Str::macro('ie', function (string|int|null $value): string {
                return preg_replace('/[^0-9]/', '', (string) $value);
            });
        }

        // ==========================================
        // FORMATADORES DE CONTATO
        // ==========================================
        if (!Str::hasMacro('phone')) {
            Str::macro('phone', function (string|int|null $value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($value) === 11) {
                    return '(' . substr($value, 0, 2) . ') ' . substr($value, 2, 5) . '-' . substr($value, 7, 4);
                }
                if (strlen($value) === 10) {
                    return '(' . substr($value, 0, 2) . ') ' . substr($value, 2, 4) . '-' . substr($value, 6, 4);
                }
                return (string) $value;
            });
        }

        if (!Str::hasMacro('cep')) {
            Str::macro('cep', function (string|int|null $value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                return strlen($value) === 8 
                    ? substr($value, 0, 5) . '-' . substr($value, 5, 3)
                    : (string) $value;
            });
        }

        // ==========================================
        // FORMATADORES DE DATA
        // ==========================================
        if (!Str::hasMacro('data')) {
            Str::macro('data', function (string|\DateTime|null $value): string {
                return $value ? Carbon::parse($value)->format('d/m/Y') : '';
            });
        }

        if (!Str::hasMacro('datahora')) {
            Str::macro('datahora', function (string|\DateTime|null $value): string {
                return $value ? Carbon::parse($value)->format('d/m/Y H:i') : '';
            });
        }

        if (!Str::hasMacro('dataExtenso')) {
            Str::macro('dataExtenso', function (string|\DateTime|null $value): string {
                if (!$value) return '';
                $meses = [
                    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ];
                $date = Carbon::parse($value);
                return $date->format('d') . ' de ' . $meses[$date->month - 1] . ' de ' . $date->format('Y');
            });
        }

        // ==========================================
        // MANIPULAÇÃO DE TEXTOS
        // ==========================================
        if (!Str::hasMacro('truncate')) {
            Str::macro('truncate', function (string $text, int $length = 100, string $end = '...'): string {
                return Str::limit($text, $length, $end);
            });
        }

        if (!Str::hasMacro('limitWords')) {
            Str::macro('limitWords', function (string $text, int $limit = 10, string $end = '...'): string {
                return Str::words($text, $limit, $end);
            });
        }

        // ==========================================
        // STATUS (NOTA ARQUITETURAL)
        // ==========================================
        // DICA: Em um projeto DDD maduro, considere migrar estes arrays para PHP 8.1 Enums 
        // (ex: StatusPedidoEnum com métodos label(), badge(), icon()).
        // Mantemos as macros aqui para compatibilidade com views legadas.
        
        if (!Str::hasMacro('statusPedido')) {
            Str::macro('statusPedido', function (string $status): array {
                $statuses = [
                    'pendente' => ['label' => 'Pendente', 'badge' => 'warning', 'icon' => 'fa-clock'],
                    'pago' => ['label' => 'Pago', 'badge' => 'info', 'icon' => 'fa-check-circle'],
                    'processando' => ['label' => 'Processando', 'badge' => 'primary', 'icon' => 'fa-spinner'],
                    'enviado' => ['label' => 'Enviado', 'badge' => 'success', 'icon' => 'fa-truck'],
                    'entregue' => ['label' => 'Entregue', 'badge' => 'success', 'icon' => 'fa-home'],
                    'cancelado' => ['label' => 'Cancelado', 'badge' => 'danger', 'icon' => 'fa-times-circle'],
                    'reembolsado' => ['label' => 'Reembolsado', 'badge' => 'secondary', 'icon' => 'fa-undo'],
                ];
                return $statuses[$status] ?? ['label' => ucfirst($status), 'badge' => 'secondary', 'icon' => 'fa-tag'];
            });
        }

        if (!Str::hasMacro('statusPagamento')) {
            Str::macro('statusPagamento', function (string $status): array {
                $statuses = [
                    'aguardando' => ['label' => 'Aguardando', 'badge' => 'warning'],
                    'aprovado' => ['label' => 'Aprovado', 'badge' => 'success'],
                    'recusado' => ['label' => 'Recusado', 'badge' => 'danger'],
                    'cancelado' => ['label' => 'Cancelado', 'badge' => 'secondary'],
                    'em_analise' => ['label' => 'Em Análise', 'badge' => 'info'],
                    'estornado' => ['label' => 'Estornado', 'badge' => 'dark'],
                ];
                return $statuses[$status] ?? ['label' => ucfirst($status), 'badge' => 'secondary'];
            });
        }

        // ==========================================
        // UTILITÁRIOS
        // ==========================================
        if (!Str::hasMacro('generateCode')) {
            Str::macro('generateCode', function (int $length = 6): string {
                return strtoupper(Str::random($length));
            });
        }

        if (!Str::hasMacro('onlyNumbers')) {
            Str::macro('onlyNumbers', function (string|int|null $value): string {
                return preg_replace('/[^0-9]/', '', (string) $value);
            });
        }

        if (!Str::hasMacro('mask')) {
            Str::macro('mask', function (string|int|null $value, string $mask): string {
                $value = (string) $value;
                $result = '';
                $index = 0;
                $maskLength = strlen($mask);
                $valueLength = strlen($value);
                
                for ($i = 0; $i < $maskLength; $i++) {
                    if ($mask[$i] === '#') {
                        if ($index < $valueLength) {
                            $result .= $value[$index++];
                        }
                    } else {
                        $result .= $mask[$i];
                    }
                }
                return $result;
            });
        }
    }
}