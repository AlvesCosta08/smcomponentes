<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Importando clientes do CSV...');
        
        $path = base_path('Clientes.csv');
        
        if (!file_exists($path)) {
            $this->command->error("❌ Arquivo Clientes.csv não encontrado em: " . $path);
            return;
        }

        // Criar role Cliente se não existir
        $role = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        
        $file = fopen($path, 'r');
        
        if (!$file) {
            $this->command->error("❌ Não foi possível abrir o arquivo.");
            return;
        }
        
        // Ler cabeçalho
        $header = fgetcsv($file, 0, ',', '"');
        $this->command->info("📋 Cabeçalho: " . json_encode($header));
        
        $count = 0;
        $skipped = 0;
        $line = 1;
        $errors = [];

        // Processar linha por linha sem transação para evitar problemas
        while (($row = fgetcsv($file, 0, ',', '"')) !== false) {
            $line++;
            
            // Verificar se a linha tem dados
            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                $skipped++;
                continue;
            }
            
            // Extrair dados do CSV (ajuste os índices conforme seu CSV)
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
                $this->command->warn("⚠️  Email já existe: " . $email);
                $skipped++;
                continue;
            }
            
            // Processar cidade/estado
            $cidade = null;
            $estado = null;
            if (!empty($cidadeEstado) && $cidadeEstado != 'FALTA PREENCHER') {
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
            $complemento = null;
            
            if (!empty($endereco) && $endereco != 'FALTA PREENCHER') {
                // Tentar extrair número
                preg_match('/(\d+)/', $endereco, $numMatches);
                $numero = $numMatches[1] ?? null;
                
                // Extrair logradouro
                $logradouro = preg_replace('/\s*\d+.*$/', '', $endereco);
                $logradouro = trim($logradouro);
                
                // Extrair bairro e complemento
                $parts = explode(',', $endereco);
                if (count($parts) > 1) {
                    $bairro = trim($parts[1] ?? '');
                }
                if (count($parts) > 2) {
                    $complemento = trim($parts[2] ?? '');
                }
            }
            
            // Limpar telefones
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
            $celular = preg_replace('/[^0-9]/', '', $celular);
            
            // Se não tiver celular, usar telefone
            if (empty($celular) && !empty($telefone)) {
                $celular = $telefone;
            }
            
            // Limpar CEP
            $cep = preg_replace('/[^0-9]/', '', $cep);
            if (empty($cep) || $cep == 'FALTA PREENCHER') {
                $cep = null;
            }
            
            // Limpar CNPJ/CPF
            $cnpjCpf = preg_replace('/[^0-9]/', '', $cnpjCpf);
            
            // Verificar se é CPF ou CNPJ e tratar valores vazios
            $cpf = null;
            $cnpj = null;
            
            if (!empty($cnpjCpf)) {
                if (strlen($cnpjCpf) <= 11) {
                    $cpf = $cnpjCpf;
                } else {
                    $cnpj = $cnpjCpf;
                }
            }
            
            // Tratar IE vazia
            if (empty($ie) || $ie == 'FALTA PREENCHER' || $ie == 'ISENTO') {
                $ie = null;
            }
            
            try {
                // Criar o usuário
                $userData = [
                    'name' => $nomeFantasia,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'ativo' => true,
                ];
                
                // Adicionar campos opcionais apenas se tiverem valor
                if ($ie !== null) {
                    $userData['ie'] = $ie;
                }
                
                if (!empty($telefone)) {
                    $userData['telefone'] = $telefone;
                }
                
                if (!empty($celular)) {
                    $userData['celular'] = $celular;
                }
                
                if ($cpf !== null) {
                    // Verificar se CPF não está vazio e é único
                    $cpfExists = User::where('cpf', $cpf)->exists();
                    if (!$cpfExists) {
                        $userData['cpf'] = $cpf;
                    }
                }
                
                if ($cnpj !== null) {
                    // Verificar se CNPJ não está vazio e é único
                    $cnpjExists = User::where('cnpj', $cnpj)->exists();
                    if (!$cnpjExists) {
                        $userData['cnpj'] = $cnpj;
                    }
                }
                
                if (!empty($logradouro) && $logradouro != 'FALTA PREENCHER') {
                    $userData['logradouro'] = $logradouro;
                }
                
                if (!empty($numero)) {
                    $userData['numero'] = $numero;
                }
                
                if (!empty($bairro) && $bairro != 'FALTA PREENCHER') {
                    $userData['bairro'] = $bairro;
                }
                
                if (!empty($complemento) && $complemento != 'FALTA PREENCHER') {
                    $userData['complemento'] = $complemento;
                }
                
                if (!empty($cidade) && $cidade != 'FALTA PREENCHER') {
                    $userData['cidade'] = $cidade;
                }
                
                if (!empty($estado) && $estado != 'FALTA PREENCHER') {
                    $userData['estado'] = $estado;
                }
                
                if (!empty($cep)) {
                    $userData['cep'] = $cep;
                }
                
                // Criar usuário
                $user = User::create($userData);
                
                // Atribuir role Cliente
                $user->assignRole($role);
                
                $count++;
                
                if ($count % 10 === 0) {
                    $this->command->info("📊 {$count} clientes importados...");
                }
                
            } catch (\Exception $e) {
                $errorMsg = "Linha {$line}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->command->error("❌ " . $errorMsg);
                $skipped++;
                
                // Continuar mesmo com erro
                continue;
            }
        }
        
        fclose($file);
        
        // Resumo
        $this->command->info("\n✅ Importação concluída!");
        $this->command->info("📊 {$count} clientes importados com sucesso!");
        $this->command->info("⏭️  {$skipped} registros pulados");
        
        if (!empty($errors)) {
            $this->command->warn("\n⚠️  Erros encontrados:");
            foreach ($errors as $error) {
                $this->command->warn("  - " . $error);
            }
        }
    }
}