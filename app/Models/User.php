<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // 🔥 Soft Delete
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes; // 🔥 Adicionado SoftDeletes

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'celular',          // 🔥 NOVO
        'ie',               // 🔥 NOVO (Inscrição Estadual)
        'cpf',
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
        'deleted_at' => 'datetime', // 🔥 NOVO (SoftDelete)
    ];

    // ========== VALIDAÇÕES ==========

    /**
     * 🔥 Validação de CPF
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
     * 🔥 NOVO: Validação de CNPJ
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
        
        for ($t = 12; $t < 14; $t++) {
            $d = 0;
            $c = 0;
            for ($m = $t - 7, $i = 0; $i < $t; $i++) {
                $d += $cnpj[$i] * $m;
                $m = ($m == 2 ? 9 : $m - 1);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$i] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 🔥 NOVO: Validação de email
     */
    public static function validarEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ========== ACESSORS ==========

    /**
     * 🔥 Formatar CPF
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
     * 🔥 NOVO: Formatar CNPJ
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
     * 🔥 NOVO: Formatar Telefone
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
     * 🔥 NOVO: Formatar Celular
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
     * 🔥 NOVO: Formatar CEP
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
     * 🔥 NOVO: Endereço completo
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
     * 🔥 NOVO: Nome completo (já existe name, mas pode ser útil)
     */
    public function getNomeCompletoAttribute(): string
    {
        return $this->name;
    }

    // ========== RELATIONSHIPS ==========

    /**
     * 🔥 NOVO: Relação com pedidos
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * 🔥 NOVO: Relação com wishlist
     */
    public function wishlist()
    {
        return $this->belongsToMany(Produto::class, 'wishlists', 'user_id', 'produto_id')
                    ->withTimestamps();
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

    /**
     * 🔥 NOVO: Verifica se tem permissão específica
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    // ========== STATUS METHODS ==========

    /**
     * 🔥 NOVO: Verifica se está ativo
     */
    public function isActive(): bool
    {
        return $this->ativo && !$this->trashed();
    }

    /**
     * 🔥 NOVO: Verifica se está deletado
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * 🔥 NOVO: Restaurar usuário
     */
    public function restoreUser(): bool
    {
        if ($this->trashed()) {
            return $this->restore();
        }
        return false;
    }

    /**
     * 🔥 NOVO: Excluir permanentemente
     */
    public function forceDeleteUser(): bool
    {
        if ($this->trashed()) {
            return $this->forceDelete();
        }
        return false;
    }

    /**
     * 🔥 NOVO: Ativar usuário
     */
    public function activate(): bool
    {
        return $this->update(['ativo' => true]);
    }

    /**
     * 🔥 NOVO: Desativar usuário
     */
    public function deactivate(): bool
    {
        return $this->update(['ativo' => false]);
    }

    /**
     * 🔥 NOVO: Registrar último acesso
     */
    public function recordLogin(): bool
    {
        return $this->update(['ultimo_acesso' => now()]);
    }

    // ========== SCOPE METHODS ==========

    /**
     * 🔥 NOVO: Scope para usuários ativos
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', true)->whereNull('deleted_at');
    }

    /**
     * 🔥 NOVO: Scope para usuários inativos
     */
    public function scopeInactive($query)
    {
        return $query->where('ativo', false)->whereNull('deleted_at');
    }

    /**
     * 🔥 NOVO: Scope para clientes
     */
    public function scopeClientes($query)
    {
        return $query->role('Cliente');
    }

    /**
     * 🔥 NOVO: Scope para administradores
     */
    public function scopeAdmins($query)
    {
        return $query->role('Admin');
    }

    /**
     * 🔥 NOVO: Scope para funcionários
     */
    public function scopeFuncionarios($query)
    {
        return $query->role('Funcionario');
    }

    /**
     * 🔥 NOVO: Scope para buscar por nome ou email
     */
    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('name', 'ILIKE', "%{$termo}%")
              ->orWhere('email', 'ILIKE', "%{$termo}%")
              ->orWhere('cpf', 'ILIKE', "%{$termo}%")
              ->orWhere('telefone', 'ILIKE', "%{$termo}%");
        });
    }

    /**
     * 🔥 NOVO: Scope para usuários que não acessam há X dias
     */
    public function scopeInativosHa($query, int $dias)
    {
        return $query->where('ultimo_acesso', '<', now()->subDays($dias))
                     ->orWhereNull('ultimo_acesso');
    }

    // ========== BOOT ==========

    protected static function booted()
    {
        // 🔥 Atribuir role padrão ao criar
        static::created(function ($user) {
            if (!$user->hasAnyRole(['Admin', 'Funcionario', 'Cliente'])) {
                $user->assignRole('Cliente');
            }
            
            Log::info('Novo usuário criado: ' . $user->email);
        });

        // 🔥 Log de atividade ao atualizar
        static::updated(function ($user) {
            if ($user->isDirty('ativo')) {
                Log::info('Usuário ' . $user->email . ' foi ' . ($user->ativo ? 'ativado' : 'desativado'));
            }
            
            if ($user->isDirty('password')) {
                Log::info('Senha alterada para o usuário: ' . $user->email);
            }
        });

        // 🔥 NOVO: Evento ao deletar (soft delete)
        static::deleting(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi movido para a lixeira");
        });

        // 🔥 NOVO: Evento ao restaurar
        static::restoring(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi restaurado");
        });

        // 🔥 NOVO: Evento ao forçar exclusão
        static::forceDeleting(function ($user) {
            Log::info("Usuário {$user->id} - {$user->email} foi excluído permanentemente");
        });
    }
}