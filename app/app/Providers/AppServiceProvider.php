<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // NÃO COLOQUE NENHUM CÓDIGO AQUI
    }

    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
        date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));

        $this->registerMacros();
    }

    protected function registerMacros(): void
    {
        // ---------- FORMATADORES MONETÁRIOS ----------
        if (!Str::hasMacro('currency')) {
            Str::macro('currency', function ($value): string {
                return 'R$ ' . number_format((float) $value, 2, ',', '.');
            });
        }

        if (!Str::hasMacro('price')) {
            Str::macro('price', function ($value): string {
                return 'R$ ' . number_format((float) $value, 2, ',', '.');
            });
        }

        // ---------- FORMATADORES DE DOCUMENTOS ----------
        if (!Str::hasMacro('cpf')) {
            Str::macro('cpf', function ($value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($value) === 11) {
                    return substr($value, 0, 3) . '.' . 
                           substr($value, 3, 3) . '.' . 
                           substr($value, 6, 3) . '-' . 
                           substr($value, 9, 2);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('cnpj')) {
            Str::macro('cnpj', function ($value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($value) === 14) {
                    return substr($value, 0, 2) . '.' . 
                           substr($value, 2, 3) . '.' . 
                           substr($value, 5, 3) . '/' . 
                           substr($value, 8, 4) . '-' . 
                           substr($value, 12, 2);
                }
                return $value;
            });
        }

        // ---------- FORMATADORES DE TELEFONE ----------
        if (!Str::hasMacro('phone')) {
            Str::macro('phone', function ($value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($value) === 11) {
                    return '(' . substr($value, 0, 2) . ') ' . 
                           substr($value, 2, 5) . '-' . 
                           substr($value, 7, 4);
                }
                if (strlen($value) === 10) {
                    return '(' . substr($value, 0, 2) . ') ' . 
                           substr($value, 2, 4) . '-' . 
                           substr($value, 6, 4);
                }
                return $value;
            });
        }

        // ---------- FORMATADORES DE ENDEREÇO ----------
        if (!Str::hasMacro('cep')) {
            Str::macro('cep', function ($value): string {
                $value = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($value) === 8) {
                    return substr($value, 0, 5) . '-' . substr($value, 5, 3);
                }
                return $value;
            });
        }

        // ---------- FORMATADORES DE DATA ----------
        if (!Str::hasMacro('data')) {
            Str::macro('data', function ($value): string {
                return $value ? Carbon::parse($value)->format('d/m/Y') : '';
            });
        }

        if (!Str::hasMacro('datahora')) {
            Str::macro('datahora', function ($value): string {
                return $value ? Carbon::parse($value)->format('d/m/Y H:i') : '';
            });
        }

        if (!Str::hasMacro('dataExtenso')) {
            Str::macro('dataExtenso', function ($value): string {
                if (!$value) return '';
                $meses = [
                    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ];
                $date = Carbon::parse($value);
                return $date->format('d') . ' de ' . $meses[$date->month - 1] . ' de ' . $date->format('Y');
            });
        }

        // ---------- MANIPULAÇÃO DE TEXTOS ----------
        if (!Str::hasMacro('slug')) {
            Str::macro('slug', function ($text): string {
                $text = preg_replace('/[^a-zA-Z0-9\s]/', '', (string) $text);
                $text = strtolower(trim($text));
                $text = preg_replace('/\s+/', '-', $text);
                return $text;
            });
        }

        if (!Str::hasMacro('truncate')) {
            Str::macro('truncate', function ($text, int $length = 100, string $end = '...'): string {
                $text = (string) $text;
                if (strlen($text) <= $length) return $text;
                return substr($text, 0, $length) . $end;
            });
        }

        if (!Str::hasMacro('limitWords')) {
            Str::macro('limitWords', function ($text, int $limit = 10, string $end = '...'): string {
                $text = (string) $text;
                $words = explode(' ', $text);
                if (count($words) <= $limit) return $text;
                return implode(' ', array_slice($words, 0, $limit)) . $end;
            });
        }

        // ---------- STATUS DE PEDIDO ----------
        if (!Str::hasMacro('statusPedido')) {
            Str::macro('statusPedido', function ($status): array {
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

        // ---------- STATUS DE PAGAMENTO ----------
        if (!Str::hasMacro('statusPagamento')) {
            Str::macro('statusPagamento', function ($status): array {
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

        // ---------- UTILITÁRIOS ----------
        if (!Str::hasMacro('generateCode')) {
            Str::macro('generateCode', function (int $length = 6): string {
                return strtoupper(Str::random($length));
            });
        }

        if (!Str::hasMacro('onlyNumbers')) {
            Str::macro('onlyNumbers', function ($value): string {
                return preg_replace('/[^0-9]/', '', (string) $value);
            });
        }

        if (!Str::hasMacro('mask')) {
            Str::macro('mask', function ($value, string $mask): string {
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

    public function provides(): array
    {
        return [];
    }
}