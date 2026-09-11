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
            $this->command->error("❌ Arquivo não encontrado: {$path}");
            return;
        }

        $role = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);

        $file   = fopen($path, 'r');
        $header = fgetcsv($file, 0, ',');
        $this->command->info("📋 Cabeçalho: " . json_encode($header, JSON_UNESCAPED_UNICODE));

        $count       = 0;
        $skipped     = 0;
        $invalidCnpj = 0;
        $line        = 1;
        $cnpjsUsados = [];

        while (($row = fgetcsv($file, 0, ',')) !== false) {
            $line++;

            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                $skipped++;
                continue;
            }

            $nomeFantasia = trim($row[0] ?? '');
            $razaoSocial  = trim($row[1] ?? '');
            $cnpjCpf      = trim($row[2] ?? '');
            $ie           = trim($row[3] ?? '');
            $email        = trim($row[4] ?? '');
            $endereco     = trim($row[5] ?? '');
            $cep          = trim($row[6] ?? '');
            $cidadeEstado = trim($row[7] ?? '');
            $telefone     = trim($row[8] ?? '');
            $celular      = trim($row[9] ?? '');

            if (empty($nomeFantasia)) {
                $skipped++;
                continue;
            }

            // =============================================
            // EMAIL
            // =============================================
            if (empty($email)) {
                $baseEmail = Str::slug($nomeFantasia, '.');
                if (empty($baseEmail)) {
                    $baseEmail = 'cliente' . $line;
                }
                $email = $baseEmail . '@cliente.com';

                $counter       = 1;
                $emailOriginal = $email;
                while (User::where('email', $email)->exists() && $counter < 9999) {
                    $email = preg_replace('/@/', $counter . '@', $emailOriginal, 1);
                    $counter++;
                }
            }

            if (User::where('email', $email)->exists()) {
                $this->command->warn("⚠️ Email já existe, pulando: {$email} (linha {$line})");
                $skipped++;
                continue;
            }

            // =============================================
            // CNPJ (obrigatório) — apenas limpa, o VO valida
            // =============================================
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpjCpf);

            if (empty($cnpjLimpo)) {
                $this->command->warn("⚠️ Linha {$line} sem CNPJ — pulando cliente.");
                $skipped++;
                continue;
            }

            if (strlen($cnpjLimpo) !== 14) {
                $this->command->warn("⚠️ Linha {$line} CNPJ com tamanho inválido ({$cnpjLimpo}) — pulando.");
                $invalidCnpj++;
                $skipped++;
                continue;
            }

            // Verifica duplicidade no CSV
            if (in_array($cnpjLimpo, $cnpjsUsados, true)) {
                $this->command->warn("⚠️ Linha {$line} CNPJ duplicado no CSV: {$cnpjLimpo} — pulando.");
                $skipped++;
                continue;
            }

            // Verifica se já existe no banco
            if (User::where('cnpj', $cnpjLimpo)->exists()) {
                $this->command->warn("⚠️ Linha {$line} CNPJ já existe no banco: {$cnpjLimpo} — pulando.");
                $skipped++;
                continue;
            }

            // =============================================
            // CIDADE / ESTADO
            // =============================================
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

            // =============================================
            // ENDEREÇO
            // =============================================
            $logradouro = null;
            $numero     = null;
            $bairro     = null;

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

            // =============================================
            // TELEFONES
            // =============================================
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
            $celular  = preg_replace('/[^0-9]/', '', $celular);
            if (empty($celular) && !empty($telefone)) {
                $celular = $telefone;
            }

            // =============================================
            // CEP
            // =============================================
            $cep = preg_replace('/[^0-9]/', '', $cep);
            if (empty($cep) || $cep === 'FALTA PREENCHER') {
                $cep = null;
            }

            // =============================================
            // IE
            // =============================================
            if (empty($ie) || $ie === 'FALTA PREENCHER' || $ie === 'ISENTO') {
                $ie = null;
            }

            // =============================================
            // CRIA O USUÁRIO
            // =============================================
            try {
                $userData = [
                    'name'              => $nomeFantasia,
                    'email'             => $email,
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'cnpj'              => $cnpjLimpo,   // o VO Cnpj valida aqui
                    'ativo'             => true,
                ];

                if ($ie !== null)        $userData['ie']         = $ie;
                if (!empty($telefone))   $userData['telefone']   = $telefone;
                if (!empty($celular))    $userData['celular']    = $celular;
                if (!empty($logradouro)) $userData['logradouro'] = $logradouro;
                if (!empty($numero))     $userData['numero']     = $numero;
                if (!empty($bairro))     $userData['bairro']     = $bairro;
                if (!empty($cidade))     $userData['cidade']     = $cidade;
                if (!empty($estado))     $userData['estado']     = $estado;
                if (!empty($cep))        $userData['cep']        = $cep;

                $user = User::create($userData);
                $user->assignRole($role);
                $cnpjsUsados[] = $cnpjLimpo;
                $count++;

                if ($count % 10 === 0) {
                    $this->command->info("📊 {$count} clientes importados...");
                }
            } catch (\InvalidArgumentException $e) {
                // CNPJ rejeitado pelo Value Object
                $invalidCnpj++;
                $this->command->warn("⚠️ Linha {$line} CNPJ inválido ({$cnpjLimpo}): " . $e->getMessage());
                $skipped++;
            } catch (\Exception $e) {
                $this->command->error("❌ Erro linha {$line}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($file);

        $this->command->info("\n✅ Importação concluída!");
        $this->command->info("📊 {$count} clientes importados com sucesso!");
        $this->command->info("⏭️  {$skipped} registros pulados");
        $this->command->info("❌ {$invalidCnpj} CNPJs inválidos (pulados)");
        $this->command->info("🔐 Senha padrão: password");
    }
}