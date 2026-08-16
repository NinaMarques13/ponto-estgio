<?php

namespace App\Domains\Estagiarios\Services;

use App\Domains\Estagiarios\Models\Estagiario;
use Illuminate\Support\Facades\Log;

class EstagiarioService
{
    /**
     * Cria um novo estagiário ou atualiza se o CPF já existir.
     */
    public function criarOuAtualizar(array $dados)
    {
        try {
            return Estagiario::updateOrCreate(
                ['cpf' => $dados['cpf']],
                [
                    'nm_estagiarios' => $dados['nome'],
                    'nm_setor'       => $dados['setor'],
                    'nr_telefone'    => $dados['telefone'],
                    'nm_email'       => $dados['email'],
                    'ds_situacao'    => 1
                ]
            );
        } catch (\Exception $e) {
            Log::error("Erro ao criar/atualizar estagiário: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualiza os dados de um estagiário existente.
     */
    public function atualizar(int $id, array $dados)
    {
        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'nm_estagiarios' => $dados['nome'] ?? $estagiario->nm_estagiarios,
            'cpf'            => $dados['cpf'] ?? $estagiario->cpf,
            'nm_setor'       => $dados['setor'] ?? $estagiario->nm_setor,
            'nr_telefone'    => $dados['telefone'] ?? $estagiario->nr_telefone,
            'nm_email'       => $dados['email'] ?? $estagiario->nm_email
        ]);

        return $estagiario;
    }

    /**
     * Realiza a exclusão lógica (desativação) de um estagiário.
     */
    public function desativar(int $id)
    {
        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'ds_situacao' => false
        ]);

        return $estagiario;
    }
}
