<?php

namespace App\Domains\ControleDePonto\Services;

use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Domains\ControleDePonto\Models\Turno;
use Carbon\Carbon;

class PontoService
{
    public function calculoHoras($inicio, $fim, $estagiario_id)
    {       
        $pontos = RegistroPonto::where('estagiario_id', $estagiario_id)
            ->whereBetween('hr_registro', [$inicio, $fim])
            ->orderBy('hr_registro', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->hr_registro)->format('Y-m-d');
            });

        // Obtém o turno do estagiário para saber a carga horária diária padrão (em minutos)
        $turno = Turno::where('estagiario_id', $estagiario_id)->first();
        $minutosPadrao = 360; // 6 horas padrão se não houver turno
        if ($turno) {
            $entradaTurno = Carbon::parse($turno->hr_entrada);
            $saidaTurno = Carbon::parse($turno->hr_saida);
            if ($saidaTurno->gt($entradaTurno)) {
                $minutosPadrao = $entradaTurno->diffInMinutes($saidaTurno);
            }
        }

        $totalMinutos = 0;

        foreach ($pontos as $dia => $registros) {
            // Verifica se há alguma ocorrência que abone o dia inteiro
            $ocorrenciaAbonada = $registros->first(function ($r) {
                return in_array($r->ds_motivo, ['recesso', 'folga']) || 
                       (in_array($r->ds_motivo, ['atestado', 'dispensa']) && $r->is_abonado);
            });

            if ($ocorrenciaAbonada) {
                // Adiciona o tempo padrão do turno para esse dia
                $totalMinutos += $minutosPadrao;
            } else {
                // Caso contrário, calcula com base nas marcações de entrada e saída normais
                $entrada = $registros->firstWhere('ds_motivo', 'entrada');
                $saida = $registros->firstWhere('ds_motivo', 'saida');

                if ($entrada && $saida) {
                    $inicioPonto = Carbon::parse($entrada->hr_registro);
                    $fimPonto = Carbon::parse($saida->hr_registro);
                    if ($fimPonto->gt($inicioPonto)) {
                        $totalMinutos += $inicioPonto->diffInMinutes($fimPonto);
                    }
                }
            }
        }

        $horas = floor($totalMinutos / 60);
        $minutos = $totalMinutos % 60;
        
        return sprintf('%02dh%02dm', $horas, $minutos);
    }
}