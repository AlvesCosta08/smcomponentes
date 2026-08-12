<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClienteSeederFinal extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Importando clientes...');
        
        $path = base_path('Clientes.csv');
        
        if (!file_exists($path)) {
            $this->command->error("❌ Arquivo não encontrado!");
            return;
        }

        $role = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        
        $file = fopen($path, 'r');
        
        // Pular cabeçalho (primeira linha)
        $header = fgetcsv($file, 0, ',');
        
        $this->command->info("📋 Cabeçalho: " . json_encode($header));
        
        $count = 0;
        $skipped = 0;
        $line = 1;
        $emailsGerados = [];

        while (($row = fgetcsv($file, 0, ',')) !== false) {
            $line++;
            
            // Verificar se a linha tem dados
            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                $skipped++;
                continue;
            }
            
            // Extrair dados
            $nomeFantasia = trim($row[0] ?? '');
            $razaoSocial = trim($row[1] ?? '');
            $cnpjCpf = trim($row[2] ?? '');
            $ie = trim($row[3] ?? '');
            $email = trim($row[4] ?? '');
            $endereco = trim($row[5] ?? '');
            $cep = trim($row[6] ?? '');
            $cidadeEstado = trim($row[7] ?? '');
            $telefone = trim($row[8] ?? '');
            $celular = trim($row[9] ?? '');
            
            // Se não tiver nome, pular
            if (empty($nomeFantasia)) {
                $skipped++;
                continue;
            }
            
            // Gerar email se estiver vazio
            if (empty($email)) {
                // Gerar email a partir do nome
                $baseEmail = Str::slug($nomeFantasia, '.');
                $email = $baseEmail . '@cliente.com';
                
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
            
            // Processar cidade/estado
            $cidade = null;
            $estado = null;
            if (!empty($cidadeEstado)) {
                $parts = explode('-', $cidadeEstado);
                if (count($parts) === 2) {
                    $cidade = trim($parts[0]);
                    $estado = trim($parts[1]);
                } else {
                    $cidade = $cidadeEstado;
                }
            }
            
            // Processar endereço
            $logradouro = null;
            $numero = null;
            $bairro = null;
            if (!empty($endereco)) {
                // Tentar extrair número
                preg_match('/(\d+)/', $endereco, $numMatches);
                $numero = $numMatches[1] ?? null;
                
                // Extrair logradouro (parte antes do número)
                $logradouro = preg_replace('/\s*\d+.*$/', '', $endereco);
                $logradouro = trim($logradouro);
                
                // Extrair bairro
                $parts = explode(',', $endereco);
                if (count($parts) > 1) {
                    $bairro = trim($parts[1] ?? '');
                }
            }
            
            // Limpar telefones
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
            $celular = preg_replace('/[^0-9]/', '', $celular);
            
            // Se não tiver celular, usar telefone
            if (empty($celular) && !empty($telefone)) {
                $celular = $telefone;
            }
            
            try {
                // Criar o usuário
                $user = User::create([
                    'name' => $nomeFantasia,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'ie' => $ie !== 'ISENTO' ? $ie : null,
                    'telefone' => $telefone,
                    'celular' => $celular,
                    'cpf' => preg_replace('/[^0-9]/', '', $cnpjCpf),
                    'logradouro' => $logradouro,
                    'numero' => $numero,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'cep' => preg_replace('/[^0-9]/', '', $cep),
                    'ativo' => true,
                    'email_verified_at' => now(),
                ]);
                
                // Atribuir role Cliente
                $user->assignRole($role);
                
                $count++;
                
                if ($count % 10 === 0) {
                    $this->command->info("📊 {$count} clientes importados...");
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Erro linha {$line} ({$email}): " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($file);
        
        $this->command->info("\n✅ Importação concluída!");
        $this->command->info("📊 {$count} clientes importados com sucesso!");
        $this->command->info("⏭️  {$skipped} registros pulados");
    }
}