<?php

namespace App\Services;

use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnonymizationService
{
    protected string $salt;

    public function __construct()
    {
        // Utiliza a chave da aplicação como salt para hashing seguro
        $this->salt = config('app.key') ?: 'ponto-estagio-default-salt-key';
    }

    /**
     * Aplica criptografia não reversível (hashing HMAC-SHA256) sobre um dado sensível.
     * Retorna uma representação hexadecimal que não pode ser desfeita matematicamente.
     */
    public function hashSensitiveData(string $data): string
    {
        return hash_hmac('sha256', trim($data), $this->salt);
    }

    /**
     * Gera um CPF numérico pseudo-anonimizado e irreversível de 11 dígitos.
     * Mantém o formato esperado pelo banco de dados (11 caracteres numéricos)
     * e garante unicidade determinística sem que seja possível descobrir o CPF original.
     */
    public function generateIrreversibleCpf(string $originalCpf, int $id): string
    {
        $hash = $this->hashSensitiveData($originalCpf . '_' . $id);
        // Extrai dígitos numéricos do hash
        $numeros = preg_replace('/\D/', '', $hash);
        
        // Garante que tenha pelo menos 11 dígitos
        if (strlen($numeros) < 11) {
            $numeros = str_pad($numeros, 11, '7', STR_PAD_RIGHT);
        }

        return substr($numeros, 0, 11);
    }

    /**
     * Mascara visualmente um CPF para exibição segura (Ex: ***.456.789-**).
     */
    public function maskCpf(?string $cpf): string
    {
        if (empty($cpf)) {
            return '---';
        }

        $digits = preg_replace('/\D/', '', $cpf);
        if (strlen($digits) !== 11) {
            return '***.***.***-**';
        }

        return '***.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-**';
    }

    /**
     * Mascara um e-mail para exibição ou anonimização parcial (Ex: j***o@pm.pr.gov.br).
     */
    public function maskEmail(?string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '***@pm.pr.gov.br';
        }

        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = str_repeat('*', $len);
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', $len - 2) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * Mascara um número de telefone (Ex: (41) 9****-**88).
     */
    public function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '---';
        }

        $digits = preg_replace('/\D/', '', $phone);
        $len = strlen($digits);

        if ($len === 11) {
            // Formato celular (XX) 9XXXX-XXXX
            return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 1) . '****-**' . substr($digits, 9, 2);
        } elseif ($len === 10) {
            // Formato fixo (XX) XXXX-XXXX
            return '(' . substr($digits, 0, 2) . ') ****-**' . substr($digits, 8, 2);
        }

        return '(**) *****-****';
    }

    /**
     * Anonimiza um registro de Estagiário conforme o modo escolhido:
     * - 'hash': Criptografia não reversível (hashing irreversível nos identificadores).
     * - 'faker': Dados realistas simulados pelo Faker pt_BR.
     */
    public function anonymizeEstagiario(Estagiario $estagiario, string $mode = 'faker', $faker = null): void
    {
        if ($mode === 'hash') {
            // Criptografia não reversível via Hashing HMAC-SHA256
            $hashId = substr($this->hashSensitiveData((string)$estagiario->id), 0, 8);
            $novoCpf = $this->generateIrreversibleCpf($estagiario->cpf, $estagiario->id);
            $novoEmail = 'anonimo_' . substr($this->hashSensitiveData($estagiario->cpf . '_email'), 0, 10) . '@pm.pr.gov.br';

            // Garante telefone fixo neutro não colidente
            $novoTelefone = str_pad((string)$estagiario->id, 11, '0', STR_PAD_LEFT);

            $estagiario->update([
                'nm_estagiarios' => 'Estagiário Anônimo #' . strtoupper($hashId),
                'cpf'            => $novoCpf,
                'nr_telefone'    => $novoTelefone,
                'nm_email'       => $novoEmail,
            ]);
        } else {
            // Dados simulados legíveis (Faker)
            if (!$faker) {
                $faker = Faker::create('pt_BR');
            }

            // Gera CPF único simulado de 11 dígitos
            $fakeCpf = preg_replace('/\D/', '', $faker->unique()->cpf());
            $fakeTelefone = preg_replace('/\D/', '', $faker->unique()->cellphoneNumber());
            if (strlen($fakeTelefone) > 11) {
                $fakeTelefone = substr($fakeTelefone, 0, 11);
            } elseif ($fakeTelefone < 11) {
                $fakeTelefone = str_pad($fakeTelefone, 11, '9', STR_PAD_LEFT);
            }

            $estagiario->update([
                'nm_estagiarios' => $faker->name(),
                'cpf'            => $fakeCpf,
                'nr_telefone'    => $fakeTelefone,
                'nm_email'       => 'estagiario_' . $estagiario->id . '@anonimizado.local',
            ]);
        }
    }

    /**
     * Anonimiza registros de ponto associados, sanitizando IPs e observações médicas/pessoais.
     */
    public function anonymizeRegistroPonto(RegistroPonto $registro, string $mode = 'faker'): void
    {
        $dadosAtualizacao = [
            'ip_registro' => '127.0.0.1', // Remove IP real rastreável
        ];

        // Se contiver observação com dados de saúde ou texto livre, neutraliza
        if (!empty($registro->ds_observacao)) {
            if ($mode === 'hash') {
                $dadosAtualizacao['ds_observacao'] = '[Dado confidencial protegido - LGPD]';
            } else {
                $dadosAtualizacao['ds_observacao'] = in_array($registro->ds_motivo, ['entrada', 'saida'])
                    ? $registro->ds_motivo
                    : 'Ocorrência regular';
            }
        }

        $registro->update($dadosAtualizacao);
    }
}

