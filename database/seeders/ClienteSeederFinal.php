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
        
        // Pular cabeçalho
        $header = fgetcsv($file, 0, ',');
        $this->command->info("📋 Cabeçalho: " . json_encode($header));
        
        $count = 0;
        $skipped = 0;
        $line = 1;
        $cpfCnpfUsados = [];

        while (($row = fgetcsv($file, 0, ',')) !== false) {
            $line++;
            
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
            
            if (empty($nomeFantasia)) {
                $skipped++;
                continue;
            }
            
            // Gerar email se vazio
            if (empty($email)) {
                $baseEmail = Str::slug($nomeFantasia, '.');
                $email = $baseEmail . '@cliente.com';
                $counter = 1;
                $emailOriginal = $email;
                while (User::where('email', $email)->exists()) {
                    $email = str_replace('@', $counter . '@', $emailOriginal);
                    $counter++;
                }
            }
            
            // Verificar email duplicado
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }
            
            // Processar CPF/CNPJ - REMOVER caracteres especiais
            $cnpjCpf = preg_replace('/[^0-9]/', '', $cnpjCpf);
            
            // 🔥 SE CPF/CNPJ ESTIVER VAZIO OU JÁ USADO, NÃO INSERIR
            $cpf = null;
            $cnpj = null;
            
            if (!empty($cnpjCpf)) {
                // Verificar se já foi usado nesta execução
                if (in_array($cnpjCpf, $cpfCnpfUsados)) {
                    $this->command->warn("⚠️ CPF/CNPJ duplicado ignorado: {$cnpjCpf} (linha {$line})");
                    $skipped++;
                    continue;
                }
                
                // Verificar se já existe no banco
                $exists = User::where('cpf', $cnpjCpf)->orWhere('cnpj', $cnpjCpf)->exists();
                if ($exists) {
                    $this->command->warn("⚠️ CPF/CNPJ já existe no banco: {$cnpjCpf} (linha {$line})");
                    $skipped++;
                    continue;
                }
                
                // Definir se é CPF ou CNPJ
                if (strlen($cnpjCpf) <= 11) {
                    $cpf = $cnpjCpf;
                } else {
                    $cnpj = $cnpjCpf;
                }
                
                // Armazenar para evitar duplicatas na mesma execução
                $cpfCnpfUsados[] = $cnpjCpf;
            }
            
            // Processar cidade/estado
            $cidade = null;
            $estado = null;
            if (!empty($cidadeEstado) && $cidadeEstado !== 'FALTA PREENCHER') {
                $parts = preg_split('/[-,]/', $cidadeEstado);
                if (count($parts) >= 2) {
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
            if (!empty($endereco) && $endereco !== 'FALTA PREENCHER') {
                preg_match('/(\d+)/', $endereco, $numMatches);
                $numero = $numMatches[1] ?? null;
                $logradouro = preg_replace('/\s*\d+.*$/', '', $endereco);
                $logradouro = trim($logradouro);
                $parts = explode(',', $endereco);
                if (count($parts) > 1) {
                    $bairro = trim($parts[1] ?? '');
                }
            }
            
            // Limpar telefones
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
            $celular = preg_replace('/[^0-9]/', '', $celular);
            if (empty($celular) && !empty($telefone)) {
                $celular = $telefone;
            }
            
            // Limpar CEP
            $cep = preg_replace('/[^0-9]/', '', $cep);
            if (empty($cep) || $cep === 'FALTA PREENCHER') {
                $cep = null;
            }
            
            // IE vazia
            if (empty($ie) || $ie === 'FALTA PREENCHER' || $ie === 'ISENTO') {
                $ie = null;
            }
            
            try {
                $userData = [
                    'name' => $nomeFantasia,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'ativo' => true,
                ];
                
                // Adicionar campos apenas se tiverem valor
                if ($ie !== null) $userData['ie'] = $ie;
                if (!empty($telefone)) $userData['telefone'] = $telefone;
                if (!empty($celular)) $userData['celular'] = $celular;
                if ($cpf !== null) $userData['cpf'] = $cpf;
                if ($cnpj !== null) $userData['cnpj'] = $cnpj;
                if (!empty($logradouro)) $userData['logradouro'] = $logradouro;
                if (!empty($numero)) $userData['numero'] = $numero;
                if (!empty($bairro)) $userData['bairro'] = $bairro;
                if (!empty($cidade)) $userData['cidade'] = $cidade;
                if (!empty($estado)) $userData['estado'] = $estado;
                if (!empty($cep)) $userData['cep'] = $cep;
                
                $user = User::create($userData);
                $user->assignRole($role);
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