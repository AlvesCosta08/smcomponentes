<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Permission\Models\Role;

class AssignUserRole
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        
        // Verifica se a role 'user' existe antes de atribuir
        $role = Role::where('name', 'user')->first();
        
        if ($role) {
            $user->assignRole($role);
            
            // Log opcional para debug
            \Log::info("Role 'user' atribuída ao usuário: " . $user->email);
        } else {
            // Se a role não existir, cria automaticamente
            $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
            $user->assignRole($role);
            \Log::info("Role 'user' criada e atribuída ao usuário: " . $user->email);
        }
    }
}