<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'celular',
        'ie',
        'cpf',
        'cnpj',
        'data_nascimento',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'ativo',
        'ultimo_acesso'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'data_nascimento' => 'date',
        'ultimo_acesso' => 'datetime',
        'ativo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // ========== RELATIONSHIPS ==========

    /**
     * Relacionamento com pedidos
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * 🔥 CORRIGIDO: Relacionamento com Wishlist (lista padrão)
     */
    public function wishlist()
    {
        return $this->hasOne(Wishlist::class)->where('is_default', true);
    }

    /**
     * Relacionamento com todas as wishlists do usuário
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Relacionamento com produtos via wishlist (para compatibilidade)
     */
    public function wishlistProdutos()
    {
        return $this->belongsToMany(Produto::class, 'wishlist_items', 'wishlist_id', 'produto_id')
                    ->via('wishlist');
    }

    // ========== WISHLIST METHODS ==========

    /**
     * Obter ou criar wishlist padrão
     */
    public function getOrCreateWishlist(): Wishlist
    {
        $wishlist = $this->wishlist()->first();

        if (!$wishlist) {
            $wishlist = $this->wishlists()->create([
                'nome' => 'Minha Lista de Desejos',
                'is_default' => true,
                'is_public' => false,
                'descricao' => 'Lista padrão de desejos',
            ]);
        }

        return $wishlist;
    }

    /**
     * Adicionar produto à wishlist
     */
    public function addToWishlist(int $produtoId): bool
    {
        try {
            $wishlist = $this->getOrCreateWishlist();
            $wishlist->addProduct($produtoId);
            return true;
        } catch (\Exception $e) {
            Log::warning('Erro ao adicionar produto à wishlist', [
                'user_id' => $this->id,
                'produto_id' => $produtoId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remover produto da wishlist
     */
    public function removeFromWishlist(int $produtoId): bool
    {
        $wishlist = $this->wishlist()->first();

        if (!$wishlist) {
            return false;
        }

        return $wishlist->removeProduct($produtoId);
    }

    /**
     * Verificar se produto está na wishlist
     */
    public function isInWishlist(int $produtoId): bool
    {
        $wishlist = $this->wishlist()->first();

        if (!$wishlist) {
            return false;
        }

        return $wishlist->hasProduct($produtoId);
    }

    /**
     * Obter contagem da wishlist
     */
    public function getWishlistCount(): int
    {
        $wishlist = $this->wishlist()->first();

        if (!$wishlist) {
            return 0;
        }

        return $wishlist->countItems();
    }

    /**
     * 🔥 Atributo para contagem da wishlist (usado no dashboard)
     */
    public function getWishlistCountAttribute(): int
    {
        return $this->getWishlistCount();
    }

    /**
     * Limpar wishlist
     */
    public function clearWishlist(): bool
    {
        $wishlist = $this->wishlist()->first();

        if (!$wishlist) {
            return false;
        }

        return $wishlist->clearItems();
    }

    // ========== VALIDAÇÕES ==========

    /**
     * Validação de CPF
     */
    public static function validarCpf($cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }
        
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validação de CNPJ
     */
    public static function validarCnpj($cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Primeiro dígito verificador
        $pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;
        
        // Segundo dígito verificador
        $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;
        
        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }

    /**
     * Validação de email
     */
    public static function validarEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida documento (CPF ou CNPJ)
     */
    public static function validarDocumento($documento): bool
    {
        $doc = preg_replace('/[^0-9]/', '', $documento);
        
        if (strlen($doc) === 11) {
            return self::validarCpf($doc);
        } elseif (strlen($doc) === 14) {
            return self::validarCnpj($doc);
        }
        
        return false;
    }

    /**
     * Detecta o tipo de documento
     */
    public static function detectarDocumento($documento): string
    {
        $doc = preg_replace('/[^0-9]/', '', $documento);
        
        if (strlen($doc) === 11) {
            return 'cpf';
        } elseif (strlen($doc) === 14) {
            return 'cnpj';
        }
        
        return 'desconhecido';
    }

    // ========== ACCESSORS ==========

    /**
     * Formatar CPF
     */
    public function getCpfFormatadoAttribute(): string
    {
        if (!$this->cpf) {
            return '';
        }
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Formatar CNPJ
     */
    public function getCnpjFormatadoAttribute(): string
    {
        if (!$this->cnpj) {
            return '';
        }
        $cnpj = preg_replace('/[^0-9]/', '', $this->cnpj);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Formatar Telefone
     */
    public function getTelefoneFormatadoAttribute(): string
    {
        if (!$this->telefone) {
            return '';
        }
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
        } elseif (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
        }
        
        return $this->telefone;
    }

    /**
     * Formatar Celular
     */
    public function getCelularFormatadoAttribute(): string
    {
        if (!$this->celular) {
            return '';
        }
        $celular = preg_replace('/[^0-9]/', '', $this->celular);
        
        if (strlen($celular) === 11) {
            return '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 5) . '-' . substr($celular, 7, 4);
        }
        
        return $this->celular;
    }

    /**
     * Formatar CEP
     */
    public function getCepFormatadoAttribute(): string
    {
        if (!$this->cep) {
            return '';
        }
        $cep = preg_replace('/[^0-9]/', '', $this->cep);
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    /**
     * Endereço completo
     */
    public function getEnderecoCompletoAttribute(): string
    {
        $parts = [];
        
        if ($this->logradouro) {
            $parts[] = $this->logradouro;
        }
        if ($this->numero) {
            $parts[] = $this->numero;
        }
        if ($this->complemento) {
            $parts[] = $this->complemento;
        }
        if ($this->bairro) {
            $parts[] = $this->bairro;
        }
        if ($this->cidade) {
            $parts[] = $this->cidade;
        }
        if ($this->estado) {
            $parts[] = $this->estado;
        }
        if ($this->cep) {
            $parts[] = $this->cep_formatado;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Tipo de documento
     */
    public function getTipoDocumentoAttribute(): string
    {
        if ($this->cpf) {
            return 'CPF';
        }
        if ($this->cnpj) {
            return 'CNPJ';
        }
        return '';
    }

    /**
     * Documento formatado
     */
    public function getDocumentoFormatadoAttribute(): string
    {
        if ($this->cpf) {
            return $this->cpf_formatado;
        }
        if ($this->cnpj) {
            return $this->cnpj_formatado;
        }
        return '';
    }

    // ========== ROLE VERIFICATIONS ==========

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isFuncionario(): bool
    {
        return $this->hasRole('Funcionario');
    }

    public function isCliente(): bool
    {
        return $this->hasRole('Cliente');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    // ========== STATUS METHODS ==========

    public function isActive(): bool
    {
        return $this->ativo && !$this->trashed();
    }

    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    public function restoreUser(): bool
    {
        if ($this->trashed()) {
            return $this->restore();
        }
        return false;
    }

    public function forceDeleteUser(): bool
    {
        if ($this->trashed()) {
            return $this->forceDelete();
        }
        return false;
    }

    public function activate(): bool
    {
        return $this->update(['ativo' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['ativo' => false]);
    }

    public function recordLogin(): bool
    {
        return $this->update(['ultimo_acesso' => now()]);
    }

    // ========== SCOPE METHODS ==========

    public function scopeActive($query)
    {
        return $query->where('ativo', true)->whereNull('deleted_at');
    }

    public function scopeInactive($query)
    {
        return $query->where('ativo', false)->whereNull('deleted_at');
    }

    public function scopeClientes($query)
    {
        return $query->role('Cliente');
    }

    public function scopeAdmins($query)
    {
        return $query->role('Admin');
    }

    public function scopeFuncionarios($query)
    {
        return $query->role('Funcionario');
    }

    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('name', 'ILIKE', "%{$termo}%")
              ->orWhere('email', 'ILIKE', "%{$termo}%")
              ->orWhere('cpf', 'ILIKE', "%{$termo}%")
              ->orWhere('cnpj', 'ILIKE', "%{$termo}%")
              ->orWhere('telefone', 'ILIKE', "%{$termo}%");
        });
    }

    public function scopeInativosHa($query, int $dias)
    {
        return $query->where('ultimo_acesso', '<', now()->subDays($dias))
                     ->orWhereNull('ultimo_acesso');
    }

    // ========== BOOT ==========

    protected static function booted()
    {
        static::created(function ($user) {
            if (!$user->hasAnyRole(['Admin', 'Funcionario', 'Cliente'])) {
                $user->assignRole('Cliente');
            }
            
            // 🔥 Criar wishlist automaticamente para novos usuários
            $user->getOrCreateWishlist();
            
            Log::info('Novo usuário criado: ' . $user->email);
        });

        static::updated(function ($user) {
            if ($user->isDirty('ativo')) {
                Log::info('Usuário ' . $user->email . ' foi ' . ($user->ativo ? 'ativado' : 'desativado'));
            }
            
            if ($user->isDirty('password')) {
                Log::info('Senha alterada para o usuário: ' . $user->email);
            }
        });

        static::deleting(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi movido para a lixeira");
        });

        static::restoring(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi restaurado");
        });

        static::forceDeleting(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi excluído permanentemente");
        });
    }
}
