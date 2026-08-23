<?php

namespace App\Models;

use App\Domain\Usuarios\ValueObjects\Cpf;
use App\Domain\Usuarios\ValueObjects\Cnpj;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'telefone', 'celular', 'ie', 'cpf', 'cnpj',
        'data_nascimento', 'cep', 'logradouro', 'numero', 'complemento', 'bairro',
        'cidade', 'estado', 'ativo', 'ultimo_acesso'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'data_nascimento' => 'date',
        'ultimo_acesso' => 'datetime',
        'ativo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function pedidos() { return $this->hasMany(Pedido::class); }
    public function wishlist() { return $this->hasOne(Wishlist::class)->where('is_default', true); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }

    // ==========================================
    // VALUE OBJECTS: CPF e CNPJ (DDD)
    // ==========================================

    public function setCpfAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['cpf'] = null;
            return;
        }
        try {
            // Salva apenas os números no banco
            $this->attributes['cpf'] = (new Cpf($value))->number();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function getCpfFormatadoAttribute(): string
    {
        if (empty($this->attributes['cpf'])) return '';
        return (new Cpf($this->attributes['cpf']))->formatado();
    }

    public function setCnpjAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['cnpj'] = null;
            return;
        }
        try {
            $this->attributes['cnpj'] = (new Cnpj($value))->number();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function getCnpjFormatadoAttribute(): string
    {
        if (empty($this->attributes['cnpj'])) return '';
        return (new Cnpj($this->attributes['cnpj']))->formatado();
    }

    public function getTipoDocumentoAttribute(): string
    {
        if (!empty($this->attributes['cpf'])) return 'CPF';
        if (!empty($this->attributes['cnpj'])) return 'CNPJ';
        return '';
    }

    public function getDocumentoFormatadoAttribute(): string
    {
        if (!empty($this->attributes['cpf'])) return $this->cpf_formatado;
        if (!empty($this->attributes['cnpj'])) return $this->cnpj_formatado;
        return '';
    }

    // ==========================================
    // MÉTODOS DE DOMÍNIO / APLICAÇÃO (Thin)
    // ==========================================

    public function getOrCreateWishlist(): Wishlist
    {
        return $this->wishlist()->firstOrCreate([
            'is_default' => true,
        ], [
            'nome' => 'Minha Lista de Desejos',
            'is_public' => false,
            'descricao' => 'Lista padrão de desejos',
        ]);
    }

    public function isAdmin(): bool { return $this->hasRole('Admin'); }
    public function isFuncionario(): bool { return $this->hasRole('Funcionario'); }
    public function isCliente(): bool { return $this->hasRole('Cliente'); }
    public function isActive(): bool { return $this->ativo && !$this->trashed(); }

    public function scopeActive($query) { return $query->where('ativo', true)->whereNull('deleted_at'); }
    public function scopeClientes($query) { return $query->role('Cliente'); }
    public function scopeAdmins($query) { return $query->role('Admin'); }

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

    protected static function booted()
    {
        static::created(function ($user) {
            if (!$user->hasAnyRole(['Admin', 'Funcionario', 'Cliente'])) {
                $user->assignRole('Cliente');
            }
            $user->getOrCreateWishlist();
            Log::info('Novo usuário criado: ' . $user->email);
        });

        static::updating(function ($user) {
            if ($user->isDirty('ativo')) {
                Log::info('Usuário ' . $user->email . ' foi ' . ($user->ativo ? 'ativado' : 'desativado'));
            }
        });
    }
}