<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Importando clientes...');
        
        $path = base_path('Clientes.csv');
        
        if (!file_exists($path)) {
            $this->command->error("❌ Arquivo Clientes.csv não encontrado!");
            return;
        }

        // Criar role Cliente se não existir
        $role = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        
        $file = fopen($path, 'r');
        
        // Pular cabeçalho com escape parameter para evitar warning
        $header = fgetcsv($file, 0, ',', '"', '\\');
        
        $count = 0;
        $skipped = 0;
        $line = 1;

        while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
            $line++;
            
            // Verificar se a linha tem dados
            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                $skipped++;
                continue;
            }
            
            $nome = trim($row[0] ?? '');
            $email = trim($row[4] ?? '');
            
            // Gerar email se estiver vazio
            if (empty($email)) {
                $email = Str::slug($nome, '.') . '@cliente.com';
                
                // Garantir unicidade
                $counter = 1;
                $emailOriginal = $email;
                while (User::where('email', $email)->exists()) {
                    $email = str_replace('@', $counter . '@', $emailOriginal);
                    $counter++;
                }
            }
            
            // Verificar se o email já existe
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }
            
            try {
                User::create([
                    'name' => $nome,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'ie' => trim($row[3] ?? ''),
                    'telefone' => preg_replace('/[^0-9]/', '', trim($row[8] ?? '')),
                    'celular' => preg_replace('/[^0-9]/', '', trim($row[9] ?? '')),
                    'ativo' => true,
                    'email_verified_at' => now(),
                ]);
                
                $count++;
                
                if ($count % 10 === 0) {
                    $this->command->info("📊 {$count} clientes importados...");
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Erro linha {$line}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($file);
        
        $this->command->info("\n✅ Importação concluída!");
        $this->command->info("📊 {$count} clientes importados com sucesso!");
        $this->command->info("⏭️  {$skipped} registros pulados");
    }
}