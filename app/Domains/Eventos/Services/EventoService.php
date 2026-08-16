<?php

namespace App\Domains\Eventos\Services;

use App\Domains\ControleDePonto\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventoService
{
    /**
     * Gera ocorrências em lote, lidando com transações e verificações de correção/abono.
     */
    public function gerarOcorrenciaEmMassa(array $dados, string $ipRegistro)
    {
        $inicio = Carbon::parse($dados['data_inicio']);
        $fim = Carbon::parse($dados['data_fim']);
        $period = CarbonPeriod::create($inicio, $fim);
        $motivo = $dados['motivo'];
        $estagiarioId = $dados['estagiario_id'];

        DB::transaction(function () use ($period, $dados, $motivo, $estagiarioId, $ipRegistro) {
            foreach ($period as $date) {
                $dataData = $date->format('Y-m-d');

                if ($motivo === 'correcao') {
                    $this->processarCorrecao($dados, $dataData, $estagiarioId, $ipRegistro);
                } else {
                    $this->processarOcorrenciaNormal($dados, $dataData, $estagiarioId, $ipRegistro, $motivo, $date);
                }
            }
        });
    }

    private function processarCorrecao(array $dados, string $dataData, int $estagiarioId, string $ipRegistro)
    {
        if (!empty($dados['hora_entrada'])) {
            // Remove a entrada existente do dia
            RegistroPonto::where('estagiario_id', $estagiarioId)
                ->whereDate('hr_registro', $dataData)
                ->where('ds_motivo', 'entrada')
                ->delete();

            $dataEntrada = Carbon::parse($dataData . ' ' . $dados['hora_entrada']);
            
            RegistroPonto::create([
                'estagiario_id' => $estagiarioId,
                'ds_motivo'     => 'entrada',
                'hr_registro'   => $dataEntrada,
                'ip_registro'   => $ipRegistro,
                'ds_observacao' => !empty($dados['observacao']) ? 'Correção: ' . $dados['observacao'] : 'Correção de ponto (entrada)',
                'is_abonado'    => false,
            ]);
        }

        if (!empty($dados['hora_saida'])) {
            // Remove a saída existente do dia
            RegistroPonto::where('estagiario_id', $estagiarioId)
                ->whereDate('hr_registro', $dataData)
                ->where('ds_motivo', 'saida')
                ->delete();

            $dataSaida = Carbon::parse($dataData . ' ' . $dados['hora_saida']);
            
            RegistroPonto::create([
                'estagiario_id' => $estagiarioId,
                'ds_motivo'     => 'saida',
                'hr_registro'   => $dataSaida,
                'ip_registro'   => $ipRegistro,
                'ds_observacao' => !empty($dados['observacao']) ? 'Correção: ' . $dados['observacao'] : 'Correção de ponto (saída)',
                'is_abonado'    => false,
            ]);
        }
    }

    private function processarOcorrenciaNormal(array $dados, string $dataData, int $estagiarioId, string $ipRegistro, string $motivo, Carbon $date)
    {
        // Deleta os registros antigos daquele dia
        RegistroPonto::where('estagiario_id', $estagiarioId)
            ->whereDate('hr_registro', $dataData)
            ->delete();

        // Determina se é abonado
        $isAbonado = false;
        if (in_array($motivo, ['recesso', 'folga'])) {
            $isAbonado = true;
        } elseif (in_array($motivo, ['atestado', 'dispensa'])) {
            $isAbonado = isset($dados['is_abonado']) ? (bool) $dados['is_abonado'] : false;
        }

        RegistroPonto::create([
            'estagiario_id' => $estagiarioId,
            'ds_motivo'     => $motivo,
            'hr_registro'   => $date->startOfDay(),
            'ip_registro'   => $ipRegistro,
            'ds_observacao' => $dados['observacao'] ?? null,
            'is_abonado'    => $isAbonado,
        ]);
    }
}
